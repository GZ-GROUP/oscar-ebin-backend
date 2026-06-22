<?php

namespace App\Services;

use App\Config\Database;
use PDO;

class CompanyService {

    private PDO $db;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getCompanyByUserId(int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM companies WHERE user_id = :user_id LIMIT 1");
        $stmt->execute([':user_id' => $userId]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
        return $company ?: null;
    }

    public function createCompany(int $userId, array $data): array {
        $existing = $this->getCompanyByUserId($userId);
        if ($existing) {
            throw new \Exception('Este usuario ya tiene una compañía');
        }

        $name = trim($data['name'] ?? '');
        $ruc = isset($data['ruc']) ? trim($data['ruc']) : null;

        if (!$name) {
            throw new \Exception('El campo name es requerido');
        }

        $stmt = $this->db->prepare(
            "INSERT INTO companies (user_id, name, ruc) VALUES (:user_id, :name, :ruc) RETURNING *"
        );

        $stmt->execute([
            ':user_id' => $userId,
            ':name' => $name,
            ':ruc' => $ruc,
        ]);

        $company = $stmt->fetch(PDO::FETCH_ASSOC);
        return $company ?: [];
    }

    public function updateCompanyByUserId(int $userId, array $data): array {
        $company = $this->getCompanyByUserId($userId);
        if (!$company) {
            throw new \Exception('No existe una compañía asociada a este usuario');
        }

        $fields = [];
        $params = [':id' => $company['id']];

        if (isset($data['name'])) {
            $fields[] = 'name = :name';
            $params[':name'] = trim($data['name']);
        }
        if (array_key_exists('ruc', $data)) {
            $fields[] = 'ruc = :ruc';
            $params[':ruc'] = $data['ruc'] !== null ? trim($data['ruc']) : null;
        }

        if (empty($fields)) {
            throw new \Exception('No hay campos para actualizar');
        }

        $sql = 'UPDATE companies SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id RETURNING *';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $updated = $stmt->fetch(PDO::FETCH_ASSOC);
        return $updated ?: [];
    }
}
