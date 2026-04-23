<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController {

    public function signup(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $user = new User($data['name'], $data['email'], $data['password']);

        $db = (new Database())->connect();

        $stmt = $db->prepare("INSERT INTO users (name, email, password, visits) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $user->getName(),
            $user->getEmail(),
            $user->getPassword(),
            $user->getVisits(),
        ]);

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
            $update = $db->prepare("UPDATE users SET visits = visits + 1 WHERE email = ? RETURNING visits");
            $update->execute([$data['email']]);
            $updated = $update->fetch();
            $visits = $updated ? (int)$updated['visits'] : null;

            $response->getBody()->write(json_encode([
                "message" => "Login exitoso",
                "visits" => $visits,
            ]));
        } else {
            $response->getBody()->write(json_encode(["message" => "Credenciales incorrectas"]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}