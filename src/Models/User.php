<?php

namespace App\Models;

class User {

    private ?int $id;

    private string $username;

    private string $name;

    private string $email;

    private string $password;

    private ?string $profileImageUrl;

    private bool $isCompany;

    private float $balance;

    private ?string $createdAt;

    private ?string $updatedAt;

    public function __construct(
        string $username,
        string $name,
        string $email,
        string $password,
        ?string $profileImageUrl = null,
        bool $isCompany = false,
        float $balance = 0
    ) {

        $this->id = null;

        $this->username = $username;

        $this->name = $name;

        $this->email = $email;

        $this->password = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $this->profileImageUrl = $profileImageUrl;

        $this->isCompany = $isCompany;

        $this->balance = $balance;

        $this->createdAt = null;

        $this->updatedAt = null;
    }

    // =========================
    // GETTERS
    // =========================

    public function getId(): ?int {
        return $this->id;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getProfileImageUrl(): ?string {
        return $this->profileImageUrl;
    }

    public function getIsCompany(): bool {
        return $this->isCompany;
    }

    public function getBalance(): float {
        return $this->balance;
    }

    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string {
        return $this->updatedAt;
    }

    // =========================
    // SETTERS
    // =========================

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function setProfileImageUrl(?string $url): void {
        $this->profileImageUrl = $url;
    }

    public function setBalance(float $balance): void {
        $this->balance = $balance;
    }

    public function setUpdatedAt(string $updatedAt): void {
        $this->updatedAt = $updatedAt;
    }

    // =========================
    // SERIALIZER
    // =========================

    public function toArray(): array {

        return [
            "id" => $this->id,
            "username" => $this->username,
            "name" => $this->name,
            "email" => $this->email,
            "profile_image_url" => $this->profileImageUrl,
            "is_company" => $this->isCompany,
            "balance" => $this->balance,
            "created_at" => $this->createdAt,
            "updated_at" => $this->updatedAt
        ];
    }
}