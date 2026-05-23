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
            "SELECT o.id, o.code, COALESCE(c.name, o.address) AS name, ST_Y(o.location::geometry) AS lat, ST_X(o.location::geometry) AS lng, o.address, o.status
             FROM oscars o
             LEFT JOIN companies c ON c.id = o.company_id
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
}
