<?php

namespace App\Models;

class Reward {

    private ?int $id;
    private ?int $companyId;
    private string $name;
    private float $price;
    private ?string $imageUrl;
    private ?int $stock;
    private bool $isActive;
    private ?string $validUntil;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        ?int $id,
        ?int $companyId,
        string $name,
        float $price,
        ?string $imageUrl,
        ?int $stock,
        bool $isActive,
        ?string $validUntil = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->companyId = $companyId;
        $this->name = $name;
        $this->price = $price;
        $this->imageUrl = $imageUrl;
        $this->stock = $stock;
        $this->isActive = $isActive;
        $this->validUntil = $validUntil;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'name' => $this->name,
            'price' => $this->price,
            'image_url' => $this->imageUrl,
            'stock' => $this->stock,
            'is_active' => $this->isActive,
            'valid_until' => $this->validUntil,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
