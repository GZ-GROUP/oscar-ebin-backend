<?php

namespace App\Middleware;

use App\Utils\JWTHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware {

    public function __invoke(Request $request, Handler $handler): Response {

        $response = new SlimResponse();

        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader) {

            $response->getBody()->write(json_encode([
                "message" => "Token requerido"
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {

            $jwt = new JWTHandler();

            $decoded = $jwt->validateToken($token);

            $request = $request->withAttribute('user', $decoded->data);

            return $handler->handle($request);

        } catch (\Exception $e) {

            $response->getBody()->write(json_encode([
                "message" => "Token inválido"
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }
    }
}