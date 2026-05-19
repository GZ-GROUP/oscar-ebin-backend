<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\UserService;
use App\Services\AuthService;

use App\Config\Database;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Utils\JWTHandler;

class AuthController {

public function signup(
    Request $request,
    Response $response
) {
    $data = $request->getParsedBody();

    $user = new User(
        $data['username'],
        $data['name'],
        $data['email'],
        $data['password'],
        $data['profile_image_url'] ?? null,   // was silently dropped
        (bool) ($data['is_company'] ?? false), // was silently dropped
        (float) ($data['balance'] ?? 0.0)      // was silently dropped
    );

    $userService = new UserService();
    $created = $userService->create($user);

    $payload = ["success" => $created];

    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json');
}

public function login(
    Request $request,
    Response $response
) {

    $data = $request->getParsedBody();

    $authService = new AuthService();

    $result = $authService->login(
        $data['email'],
        $data['password']
    );

    $response->getBody()->write(
        json_encode($result)
    );

    return $response
        ->withHeader(
            'Content-Type',
            'application/json'
        );
}
}