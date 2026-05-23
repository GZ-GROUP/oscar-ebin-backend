<?php

namespace App\Controllers;

use App\Services\OscarService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class OscarController {

    private OscarService $oscarService;

    public function __construct() {
        $this->oscarService = new OscarService();
    }

    public function locations(Request $request, Response $response): Response {
        $locations = $this->oscarService->getLocations();

        $response->getBody()->write(json_encode(['data' => $locations]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function createItem(Request $request, Response $response): Response {
        $body = $request->getParsedBody();

        $oscarCode = isset($body['oscar_code']) ? trim($body['oscar_code']) : null;
        $trashTypeId = isset($body['trash_type_id']) ? (int) $body['trash_type_id'] : null;
        $sessionId = isset($body['session_id']) ? (int) $body['session_id'] : null;

        if (!$oscarCode || !$trashTypeId) {
            $response->getBody()->write(json_encode(['error' => 'oscar_code y trash_type_id son requeridos']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $item = $this->oscarService->createItem($oscarCode, $trashTypeId, $sessionId);
            $response->getBody()->write(json_encode(['data' => $item]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function claim(Request $request, Response $response): Response {
        $body = $request->getParsedBody();
        $user = $request->getAttribute('user');

        $oscarCode = isset($body['oscar_code']) ? trim($body['oscar_code']) : null;
        $sessionId = isset($body['session_id']) ? (int) $body['session_id'] : null;

        if (!$oscarCode) {
            $response->getBody()->write(json_encode(['error' => 'oscar_code es requerido']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $claim = $this->oscarService->claimSessionByCode((int) $user->id, $oscarCode, $sessionId);
            $response->getBody()->write(json_encode(['data' => $claim]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}
