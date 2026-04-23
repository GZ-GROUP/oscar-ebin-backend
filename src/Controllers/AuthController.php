<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController {

    public function signup(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $user = new User($data['email'], $data['password']);

        $db = (new Database())->connect();

        $stmt = $db->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->execute([$user->getEmail(), $user->getPassword()]);

        $response->getBody()->write(json_encode(["message" => "Usuario creado"]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function login(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $db = (new Database())->connect();

        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);

        $user = $stmt->fetch();

        if ($user && password_verify($data['password'], $user['password'])) {
            $response->getBody()->write(json_encode(["message" => "Login exitoso"]));
        } else {
            $response->getBody()->write(json_encode(["message" => "Credenciales incorrectas"]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}