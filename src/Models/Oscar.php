<?php

namespace App\Models;

class Oscar {

    private ?int $id;
    private ?int $companyId;
    private ?string $code;
    private ?string $name;
    private ?string $address;
    private ?float $lat;
    private ?float $lng;
    private string $status;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        ?int $id,
        ?int $companyId,
        ?string $code,
        ?string $name,
        ?string $address,
        ?float $lat,
        ?float $lng,
        string $status,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->companyId = $companyId;
        $this->code = $code;
        $this->name = $name;
        $this->address = $address;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
