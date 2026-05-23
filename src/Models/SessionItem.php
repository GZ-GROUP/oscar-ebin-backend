<?php

namespace App\Models;

class SessionItem {

    private ?int $id;
    private int $sessionId;
    private int $trashTypeId;
    private ?string $scannedAt;

    public function __construct(
        ?int $id,
        int $sessionId,
        int $trashTypeId,
        ?string $scannedAt = null
    ) {
        $this->id = $id;
        $this->sessionId = $sessionId;
        $this->trashTypeId = $trashTypeId;
        $this->scannedAt = $scannedAt;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'session_id' => $this->sessionId,
            'trash_type_id' => $this->trashTypeId,
            'scanned_at' => $this->scannedAt,
        ];
    }
}
