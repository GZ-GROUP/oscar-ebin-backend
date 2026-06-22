<?php

namespace App\Services;

use App\Config\Database;
use PDO;
use PDOException;

class OscarService {

    private PDO $db;
    private UserService $userService;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->userService = new UserService();
    }

    public function getLocations(): array {
        $stmt = $this->db->query(
            "SELECT o.id, o.code, o.name, ST_Y(o.location::geometry) AS lat, ST_X(o.location::geometry) AS lng, o.address, o.status
             FROM oscars o
             WHERE o.status = 'active'"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createSession(int $oscarId): ?array {
        $stmt = $this->db->prepare(
            "INSERT INTO sessions (oscar_id) VALUES (:oscar_id) RETURNING *"
        );

        $stmt->execute([':oscar_id' => $oscarId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getOscarByCode(string $code): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM oscars WHERE code = :code LIMIT 1"
        );
        $stmt->execute([':code' => $code]);

        $oscar = $stmt->fetch(PDO::FETCH_ASSOC);
        return $oscar ?: null;
    }

    public function createItem(string $oscarCode, int $trashTypeId, ?int $sessionId = null): ?array {
        $oscar = $this->getOscarByCode($oscarCode);
        if (!$oscar) {
            throw new \Exception('Oscar no encontrado');
        }

        if ($sessionId) {
            $session = $this->getSessionById($sessionId);
            if (!$session) {
                throw new \Exception('Sesión no encontrada');
            }
            if ($session['status'] !== 'open') {
                throw new \Exception('Solo se pueden agregar items a sesiones abiertas');
            }
            if ($session['oscar_id'] !== $oscar['id']) {
                throw new \Exception('La sesión no pertenece a este Oscar');
            }
        } else {
            $session = $this->getOpenSession($oscar['id']);
            if (!$session) {
                $session = $this->createSession($oscar['id']);
            }
        }

        $stmt = $this->db->prepare(
            "INSERT INTO session_item (session_id, trash_type_id) VALUES (:session_id, :trash_type_id) RETURNING *"
        );

        $stmt->execute([
            ':session_id' => $session['id'],
            ':trash_type_id' => $trashTypeId,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function claimSessionByCode(int $userId, string $oscarCode, ?int $sessionId = null): array {
        $oscar = $this->getOscarByCode($oscarCode);
        if (!$oscar) {
            throw new \Exception('Oscar no encontrado');
        }

        if ($sessionId) {
            $session = $this->getSessionById($sessionId);
        } else {
            $session = $this->getOpenSession($oscar['id']);
        }

        if (!$session) {
            throw new \Exception('No existe sesión abierta para este Oscar');
        }

        if ($session['status'] !== 'open') {
            throw new \Exception('La sesión debe estar abierta para poder reclamarla');
        }

        $totals = $this->getSessionTotals($session['id']);
        $itemCount = (int) $totals['item_count'];
        $totalValue = (float) $totals['total_value'];

        if ($itemCount === 0) {
            throw new \Exception('No hay items para reclamar en la sesión');
        }

        try {
            $this->db->beginTransaction();

            $update = $this->db->prepare(
                "UPDATE sessions
                 SET user_id = :user_id,
                     total_value = :total_value,
                     status = 'completed',
                     claimed_at = NOW(),
                     ended_at = NOW()
                 WHERE id = :id"
            );

            $update->execute([
                ':user_id' => $userId,
                ':total_value' => $totalValue,
                ':id' => $session['id'],
            ]);

            if ($totalValue > 0) {
                $transaction = $this->db->prepare(
                    "INSERT INTO transactions (user_id, session_id, amount, type, note)
                     VALUES (:user_id, :session_id, :amount, 'credit', 'Puntos por sesión')"
                );

                $transaction->execute([
                    ':user_id' => $userId,
                    ':session_id' => $session['id'],
                    ':amount' => $totalValue,
                ]);

                $this->userService->updateBalance($userId, $totalValue);
            }

            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }

        return [
            'session' => $this->getSessionById($session['id']),
            'item_count' => $itemCount,
            'total_value' => $totalValue
        ];
    }

    private function getOpenSession(int $oscarId): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM sessions WHERE oscar_id = :oscar_id AND status = 'open' ORDER BY started_at DESC LIMIT 1"
        );
        $stmt->execute([':oscar_id' => $oscarId]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        return $session ?: null;
    }

    private function getSessionById(int $sessionId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM sessions WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $sessionId]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        return $session ?: null;
    }

    private function getSessionTotals(int $sessionId): array {
        $stmt = $this->db->prepare(
            "SELECT COUNT(si.id) AS item_count, COALESCE(SUM(tt.value), 0) AS total_value
             FROM session_item si
             JOIN trash_type tt ON tt.id = si.trash_type_id
             WHERE si.session_id = :session_id"
        );

        $stmt->execute([':session_id' => $sessionId]);
        $totals = $stmt->fetch(PDO::FETCH_ASSOC);

        return $totals ?: ['item_count' => 0, 'total_value' => 0.0];
    }

    // --- Nuevas funciones para gestión de oscars y estadísticas ---

    private function getCompanyIdByUserId(int $userId): ?int {
        $stmt = $this->db->prepare("SELECT id FROM companies WHERE user_id = :user_id LIMIT 1");
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    }

    public function createOscar(int $userId, array $data): array {
        $companyId = $this->getCompanyIdByUserId($userId);
        if (!$companyId) {
            throw new \Exception('Usuario no tiene empresa asociada');
        }

        $code = $data['code'] ?? null;
        $name = $data['name'] ?? null;
        $address = $data['address'] ?? null;
        $status = $data['status'] ?? 'active';
        $lat = isset($data['lat']) ? (float)$data['lat'] : null;
        $lng = isset($data['lng']) ? (float)$data['lng'] : null;

        if (!$code || !$name || !$lat || !$lng) {
            throw new \Exception('code, name, lat y lng son requeridos');
        }

        $stmt = $this->db->prepare(
            "INSERT INTO oscars (company_id, code, location, name, address, status)
             VALUES (:company_id, :code, ST_GeographyFromText(:point), :name, :address, :status) RETURNING *"
        );

        $point = sprintf('SRID=4326;POINT(%F %F)', $lng, $lat);

        $stmt->execute([
            ':company_id' => $companyId,
            ':code' => $code,
            ':point' => $point,
            ':name' => $name,
            ':address' => $address,
            ':status' => $status,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getOscarById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM oscars WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getOscarStatsById(int $oscarId): array {
        $totalsStmt = $this->db->prepare(
            "SELECT COUNT(si.id) AS total_items, COALESCE(SUM(tt.value),0) AS total_value
             FROM session_item si
             JOIN trash_type tt ON tt.id = si.trash_type_id
             JOIN sessions s ON s.id = si.session_id
             WHERE s.oscar_id = :oscar_id"
        );
        $totalsStmt->execute([':oscar_id' => $oscarId]);
        $totals = $totalsStmt->fetch(PDO::FETCH_ASSOC) ?: ['total_items' => 0, 'total_value' => 0];

        $itemsStmt = $this->db->prepare(
            "SELECT si.id, si.scanned_at, tt.name AS trash_type, tt.value
             FROM session_item si
             JOIN trash_type tt ON tt.id = si.trash_type_id
             JOIN sessions s ON s.id = si.session_id
             WHERE s.oscar_id = :oscar_id
             ORDER BY si.scanned_at DESC
             LIMIT 5"
        );
        $itemsStmt->execute([':oscar_id' => $oscarId]);
        $lastItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        $claimsStmt = $this->db->prepare(
            "SELECT s.id, s.user_id, s.total_value, s.claimed_at
             FROM sessions s
             WHERE s.oscar_id = :oscar_id AND s.status = 'completed'
             ORDER BY s.claimed_at DESC
             LIMIT 5"
        );
        $claimsStmt->execute([':oscar_id' => $oscarId]);
        $lastClaims = $claimsStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'totals' => $totals,
            'last_items' => $lastItems,
            'last_claims' => $lastClaims
        ];
    }

    public function getItemById(int $itemId): ?array {
        $stmt = $this->db->prepare(
            "SELECT si.id, si.scanned_at, si.session_id, tt.id AS trash_type_id, tt.name AS trash_type, tt.value,
                    s.oscar_id, s.started_at
             FROM session_item si
             JOIN trash_type tt ON tt.id = si.trash_type_id
             JOIN sessions s ON s.id = si.session_id
             WHERE si.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $itemId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function deleteOscarById(int $userId, int $oscarId): bool {
        $companyId = $this->getCompanyIdByUserId($userId);
        if (!$companyId) throw new \Exception('Usuario no tiene empresa asociada');

        $oscar = $this->getOscarById($oscarId);
        if (!$oscar) throw new \Exception('Oscar no encontrado');
        if ((int)$oscar['company_id'] !== $companyId) throw new \Exception('No autorizado para borrar este Oscar');

        $stmt = $this->db->prepare("DELETE FROM oscars WHERE id = :id");
        return $stmt->execute([':id' => $oscarId]);
    }

    public function updateOscarById(int $userId, int $oscarId, array $data): array {
        $companyId = $this->getCompanyIdByUserId($userId);
        if (!$companyId) throw new \Exception('Usuario no tiene empresa asociada');

        $oscar = $this->getOscarById($oscarId);
        if (!$oscar) throw new \Exception('Oscar no encontrado');
        if ((int)$oscar['company_id'] !== $companyId) throw new \Exception('No autorizado para actualizar este Oscar');

        $fields = [];
        $params = [':id' => $oscarId];

        if (isset($data['name'])) { $fields[] = 'name = :name'; $params[':name'] = $data['name']; }
        if (isset($data['address'])) { $fields[] = 'address = :address'; $params[':address'] = $data['address']; }
        if (isset($data['status'])) { $fields[] = 'status = :status'; $params[':status'] = $data['status']; }
        if (isset($data['code'])) { $fields[] = 'code = :code'; $params[':code'] = $data['code']; }
        if (isset($data['lat']) && isset($data['lng'])) {
            $point = sprintf('SRID=4326;POINT(%F %F)', (float)$data['lng'], (float)$data['lat']);
            $fields[] = 'location = ST_GeographyFromText(:point)';
            $params[':point'] = $point;
        }

        if (empty($fields)) {
            throw new \Exception('No hay campos para actualizar');
        }

        $sql = 'UPDATE oscars SET ' . implode(', ', $fields) . ' , updated_at = NOW() WHERE id = :id RETURNING *';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }
}
