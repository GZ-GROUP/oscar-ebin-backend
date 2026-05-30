<?php

namespace App\Services;

use App\Config\Database;
use PDO;

class ReportService {

    private PDO $db;
    private UserService $userService;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->userService = new UserService();
    }

    public function getRanking(int $limit = 10, int $offset = 0): array {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.username, u.name, u.profile_image_url, COALESCE(SUM(t.amount), 0) AS points
             FROM users u
             JOIN transactions t ON t.user_id = u.id
             WHERE t.type = 'credit' AND t.created_at >= NOW() - INTERVAL '1 month'
             GROUP BY u.id, u.username, u.name, u.profile_image_url
             ORDER BY points DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserHistory(int $userId, ?string $category = null, ?string $transactionType = null, int $limit = 10, int $offset = 0): array {
        $sql = "SELECT t.*, rc.code AS reward_code, r.name AS reward_name, s.oscar_id AS session_oscar_id, o.name AS oscar_name
                FROM transactions t
                LEFT JOIN reward_claim rc ON rc.id = t.reward_claim_id
                LEFT JOIN reward r ON r.id = rc.reward_id
                LEFT JOIN sessions s ON s.id = t.session_id
                LEFT JOIN oscars o ON o.id = s.oscar_id
                WHERE t.user_id = :user_id";

        if ($category === 'session') {
            $sql .= " AND t.session_id IS NOT NULL AND t.reward_claim_id IS NULL";
        } elseif ($category === 'claim') {
            $sql .= " AND t.reward_claim_id IS NOT NULL";
        }

        if ($transactionType !== null) {
            $sql .= " AND t.type = :transaction_type";
        }

        $sql .= " ORDER BY t.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        if ($transactionType !== null) {
            $stmt->bindValue(':transaction_type', $transactionType, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserProfileMetrics(int $userId): array {
        $user = $this->userService->findById($userId);
        if (!$user) {
            throw new \Exception('Usuario no encontrado');
        }

        $sessionsStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM sessions WHERE user_id = :user_id AND status = 'completed'"
        );
        $sessionsStmt->execute([':user_id' => $userId]);
        $sessionsCount = (int) $sessionsStmt->fetchColumn();

        $rewardClaimsStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM reward_claim WHERE user_id = :user_id"
        );
        $rewardClaimsStmt->execute([':user_id' => $userId]);
        $rewardClaimsCount = (int) $rewardClaimsStmt->fetchColumn();

        $pointsStmt = $this->db->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS points_earned
             FROM transactions
             WHERE user_id = :user_id AND type = 'credit'"
        );
        $pointsStmt->execute([':user_id' => $userId]);
        $pointsEarned = (float) $pointsStmt->fetchColumn();

        $typeStmt = $this->db->prepare(
            "SELECT tt.name, COUNT(si.id) AS count
             FROM session_item si
             JOIN sessions s ON s.id = si.session_id
             JOIN trash_type tt ON tt.id = si.trash_type_id
             WHERE s.user_id = :user_id AND s.status = 'completed'
             GROUP BY tt.name
             ORDER BY count DESC"
        );
        $typeStmt->execute([':user_id' => $userId]);
        $trashByType = $typeStmt->fetchAll(PDO::FETCH_ASSOC);

        $lastMonthStmt = $this->db->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS points_last_month
             FROM transactions
             WHERE user_id = :user_id AND type = 'credit' AND created_at >= NOW() - INTERVAL '1 month'"
        );
        $lastMonthStmt->execute([':user_id' => $userId]);
        $pointsLastMonth = (float) $lastMonthStmt->fetchColumn();

        return [
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'profile_image_url' => $user['profile_image_url'],
                'is_company' => $user['is_company'],
            ],
            'sessions_completed' => $sessionsCount,
            'rewards_claimed' => $rewardClaimsCount,
            'points_available' => (float) $user['balance'],
            'points_earned_total' => $pointsEarned,
            'points_earned_last_month' => $pointsLastMonth,
            'trash_items_by_type' => $trashByType,
        ];
    }
}
