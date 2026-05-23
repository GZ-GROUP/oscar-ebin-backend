<?php

namespace App\Services;

use App\Config\Database;
use PDO;
use PDOException;

class RewardService {

    private PDO $db;
    private UserService $userService;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->userService = new UserService();
    }

    public function redeemReward(int $userId, int $rewardId): array {
        $user = $this->userService->findById($userId);
        if (!$user) {
            throw new \Exception('Usuario no encontrado');
        }

        $reward = $this->findRewardById($rewardId);
        if (!$reward || !$reward['is_active']) {
            throw new \Exception('Recompensa no disponible');
        }

        if ($reward['stock'] !== null && (int) $reward['stock'] <= 0) {
            throw new \Exception('Recompensa sin stock disponible');
        }

        if ((float) $user['balance'] < (float) $reward['price']) {
            throw new \Exception('Saldo insuficiente para reclamar esta recompensa');
        }

        $code = bin2hex(random_bytes(10));

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "INSERT INTO reward_claim (user_id, reward_id, status, code)
                 VALUES (:user_id, :reward_id, 'pending', :code)
                 RETURNING *"
            );
            $stmt->execute([
                ':user_id' => $userId,
                ':reward_id' => $rewardId,
                ':code' => $code,
            ]);

            $claim = $stmt->fetch(PDO::FETCH_ASSOC);

            $transaction = $this->db->prepare(
                "INSERT INTO transactions (user_id, reward_claim_id, amount, type, note)
                 VALUES (:user_id, :reward_claim_id, :amount, 'debit', 'Redención de recompensa')"
            );
            $transaction->execute([
                ':user_id' => $userId,
                ':reward_claim_id' => $claim['id'],
                ':amount' => $reward['price'],
            ]);

            if ($reward['stock'] !== null) {
                $updateStock = $this->db->prepare(
                    "UPDATE reward SET stock = stock - 1 WHERE id = :id"
                );
                $updateStock->execute([':id' => $rewardId]);
            }

            $this->userService->updateBalance($userId, -(float) $reward['price']);

            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $claim ?: [];
    }

    private function findRewardById(int $rewardId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM reward WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $rewardId]);
        $reward = $stmt->fetch(PDO::FETCH_ASSOC);

        return $reward ?: null;
    }
}
