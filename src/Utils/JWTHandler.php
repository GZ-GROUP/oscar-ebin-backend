<?php

namespace App\Utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler {

    private $secret;
    private $expiration;
    private $refreshExpiration;

    public function __construct() {
        $this->secret = $_ENV['JWT_SECRET'];
        $this->expiration = $_ENV['JWT_EXPIRATION'];
        $this->refreshExpiration = $_ENV['JWT_REFRESH_EXPIRATION'];
    }

    public function generateAccessToken($user) {

        $payload = [
            "iss" => "oscar-api",
            "aud" => "oscar-app",
            "iat" => time(),
            "exp" => time() + $this->expiration,

            "data" => [
                "id" => $user['id'],
                "email" => $user['email'],
                "name" => $user['name'],
                "is_company" => $user['is_company']
            ]
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function generateRefreshToken($user) {

        $payload = [
            "iss" => "oscar-api",
            "aud" => "oscar-app",
            "iat" => time(),
            "exp" => time() + $this->refreshExpiration,

            "data" => [
                "id" => $user['id']
            ]
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function validateToken($token) {
        return JWT::decode($token, new Key($this->secret, 'HS256'));
    }
}