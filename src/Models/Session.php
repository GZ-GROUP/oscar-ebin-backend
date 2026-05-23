<?php

namespace App\Models;

class Session {

    private ?int $id;
    private ?int $userId;
    private int $oscarId;
    private string $status;
    private float $totalValue;
    private ?string $startedAt;
    private ?string $claimedAt;
    private ?string $endedAt;

    public function __construct(
        ?int $id,
        ?int $userId,
        int $oscarId,
        string $status,
        float $totalValue,
        ?string $startedAt = null,
        ?string $claimedAt = null,
        ?string $endedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->oscarId = $oscarId;
        $this->status = $status;
        $this->totalValue = $totalValue;
        $this->startedAt = $startedAt;
        $this->claimedAt = $claimedAt;
        $this->endedAt = $endedAt;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'oscar_id' => $this->oscarId,
            'status' => $this->status,
            'total_value' => $this->totalValue,
            'started_at' => $this->startedAt,
            'claimed_at' => $this->claimedAt,
            'ended_at' => $this->endedAt,
        ];
    }
}
