<?php

namespace App\Models;

class RewardClaim {

    private ?int $id;
    private int $userId;
    private int $rewardId;
    private string $status;
    private ?string $code;
    private ?string $createdAt;
    private ?string $redeemedAt;

    public function __construct(
        ?int $id,
        int $userId,
        int $rewardId,
        string $status,
        ?string $code = null,
        ?string $createdAt = null,
        ?string $redeemedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->rewardId = $rewardId;
        $this->status = $status;
        $this->code = $code;
        $this->createdAt = $createdAt;
        $this->redeemedAt = $redeemedAt;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'reward_id' => $this->rewardId,
            'status' => $this->status,
            'code' => $this->code,
            'created_at' => $this->createdAt,
            'redeemed_at' => $this->redeemedAt,
        ];
    }
}
