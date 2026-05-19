<?php

namespace App\Services;

use App\Services\UserService;
use App\Utils\JWTHandler;

class AuthService {

    private UserService $userService;

    private JWTHandler $jwt;

    public function __construct() {

        $this->userService = new UserService();

        $this->jwt = new JWTHandler();
    }

    public function login(
        string $email,
        string $password
    ): array {

        $user = $this->userService
            ->findByEmail($email);

        if (
            !$user ||
            !password_verify(
                $password,
                $user['password']
            )
        ) {

            return [
                "authenticated" => false
            ];
        }

        return [

            "authenticated" => true,

            "access_token" =>
                $this->jwt
                    ->generateAccessToken($user),

            "refresh_token" =>
                $this->jwt
                    ->generateRefreshToken($user),

            "user" => [
                "id" => $user['id'],
                "name" => $user['name'],
                "email" => $user['email'],
                "is_company" => $user['is_company']
            ]
        ];
    }
}