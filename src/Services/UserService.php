<?php

namespace App\Services;

use App\Config\Database;
use App\Models\User;
use PDO;
use PDOException;

class UserService {

    private PDO $db;

    public function __construct() {

        $this->db = (new Database())->connect();

        $this->db->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
    }

public function create(User $user): bool {

    try {

        $stmt = $this->db->prepare("
            INSERT INTO users (
                username,
                name,
                email,
                password,
                profile_image_url,
                is_company,
                balance
            )
            VALUES (
                :username,
                :name,
                :email,
                :password,
                :profile_image_url,
                :is_company,
                :balance
            )
        ");

        return $stmt->execute([

            ':username' =>
                trim($user->getUsername()),

            ':name' =>
                trim($user->getName()),

            ':email' =>
                strtolower(trim($user->getEmail())),

            ':password' =>
                $user->getPassword(),

            ':profile_image_url' =>
                $user->getProfileImageUrl(),

            ':is_company' =>
                (int) $user->getIsCompany(), // ← fix goes HERE, not in the SQL

            ':balance' =>
                (float) $user->getBalance()
        ]);

    } catch (PDOException $e) {

        error_log($e->getMessage());

        return false;
    }
}

    public function findByEmail(
        string $email
    ): ?array {

        $stmt = $this->db->prepare("
            SELECT *
            FROM users
            WHERE email = :email
            LIMIT 1
        ");

        $stmt->execute([
            ':email' =>
                strtolower(trim($email))
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findById(
        int $id
    ): ?array {

        $stmt = $this->db->prepare("
            SELECT *
            FROM users
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $id
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function updateBalance(
        int $userId,
        float $amount
    ): bool {

        try {

            $stmt = $this->db->prepare("
                UPDATE users
                SET
                    balance = balance + :amount,
                    updated_at = NOW()
                WHERE id = :id
            ");

            return $stmt->execute([
                ':amount' => $amount,
                ':id' => $userId
            ]);

        } catch (PDOException $e) {

            error_log($e->getMessage());

            return false;
        }
    }
}