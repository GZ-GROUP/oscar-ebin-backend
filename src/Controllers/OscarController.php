<?php

namespace App\Controllers;

use App\Services\OscarService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Exception;

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

    private function ensureCompany($request, Response $response): ?Response {
        $user = $request->getAttribute('user');
        if (!$user || empty($user->is_company) || $user->is_company != true) {
            $response->getBody()->write(json_encode(['error' => 'Operación permitida solo para cuentas empresa']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }
        return null;
    }

    public function createOscar(Request $request, Response $response): Response {
        if ($err = $this->ensureCompany($request, $response)) return $err;

        $body = $request->getParsedBody() ?: $_POST ?: $_REQUEST;

        try {
            $user = $request->getAttribute('user');
            $created = $this->oscarService->createOscar((int)$user->id, $body);
            $response->getBody()->write(json_encode(['data' => $created]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function stats(Request $request, Response $response): Response {
        if ($err = $this->ensureCompany($request, $response)) return $err;

        $params = $request->getQueryParams() ?: $_GET ?: $_REQUEST;

        $id = isset($params['id']) ? (int)$params['id'] : null;
        $code = isset($params['code']) ? trim($params['code']) : null;

        try {
            if ($code) {
                $oscar = $this->oscarService->getOscarByCode($code);
                if (!$oscar) throw new Exception('Oscar no encontrado');
                $id = $oscar['id'];
            }
            if (!$id) throw new Exception('Se requiere id o code');

            $stats = $this->oscarService->getOscarStatsById($id);
            $response->getBody()->write(json_encode(['data' => $stats]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function getItem(Request $request, Response $response): Response {
        if ($err = $this->ensureCompany($request, $response)) return $err;

        $params = $request->getQueryParams() ?: $_GET ?: $_REQUEST;
        $id = isset($params['id']) ? (int)$params['id'] : null;

        if (!$id) {
            $response->getBody()->write(json_encode(['error' => 'id es requerido']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $item = $this->oscarService->getItemById($id);
            if (!$item) {
                $response->getBody()->write(json_encode(['error' => 'Item no encontrado']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            $response->getBody()->write(json_encode(['data' => $item]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function deleteOscar(Request $request, Response $response): Response {
        if ($err = $this->ensureCompany($request, $response)) return $err;

        $params = $request->getQueryParams() ?: $_GET ?: $_REQUEST;
        $id = isset($params['id']) ? (int)$params['id'] : null;
        $code = isset($params['code']) ? trim($params['code']) : null;

        try {
            if ($code) {
                $oscar = $this->oscarService->getOscarByCode($code);
                if (!$oscar) throw new Exception('Oscar no encontrado');
                $id = $oscar['id'];
            }
            if (!$id) throw new Exception('Se requiere id o code');

            $user = $request->getAttribute('user');
            $this->oscarService->deleteOscarById((int)$user->id, $id);

            $response->getBody()->write(json_encode(['success' => true]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function updateOscar(Request $request, Response $response): Response {
        if ($err = $this->ensureCompany($request, $response)) return $err;

        $body = $request->getParsedBody() ?: $_POST ?: $_REQUEST;
        $id = isset($body['id']) ? (int)$body['id'] : (isset($body['code']) ? null : null);

        try {
            if (isset($body['code']) && !$id) {
                $existing = $this->oscarService->getOscarByCode(trim($body['code']));
                if ($existing) $id = $existing['id'];
            }

            if (!$id) throw new Exception('Se requiere id o code para actualizar');

            $user = $request->getAttribute('user');
            $updated = $this->oscarService->updateOscarById((int)$user->id, $id, $body);

            $response->getBody()->write(json_encode(['data' => $updated]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}
