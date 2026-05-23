<?php

namespace App\Models;

class Transaction {

    private ?int $id;
    private int $userId;
    private ?int $sessionId;
    private ?int $rewardClaimId;
    private float $amount;
    private string $type;
    private ?string $note;
    private ?string $createdAt;

    public function __construct(
        ?int $id,
        int $userId,
        ?int $sessionId,
        ?int $rewardClaimId,
        float $amount,
        string $type,
        ?string $note = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->sessionId = $sessionId;
        $this->rewardClaimId = $rewardClaimId;
        $this->amount = $amount;
        $this->type = $type;
        $this->note = $note;
        $this->createdAt = $createdAt;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'session_id' => $this->sessionId,
            'reward_claim_id' => $this->rewardClaimId,
            'amount' => $this->amount,
            'type' => $this->type,
            'note' => $this->note,
            'created_at' => $this->createdAt,
        ];
    }
}
