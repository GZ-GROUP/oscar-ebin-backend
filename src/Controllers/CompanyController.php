<?php

namespace App\Controllers;

use App\Services\CompanyService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class CompanyController {

    private CompanyService $companyService;

    public function __construct() {
        $this->companyService = new CompanyService();
    }

    private function ensureCompanyUser(Request $request, Response $response): ?Response {
        $user = $request->getAttribute('user');
        if (!$user || empty($user->is_company) || $user->is_company != true) {
            $response->getBody()->write(json_encode(['error' => 'Operación permitida solo para cuentas empresa']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }
        return null;
    }

    public function createCompany(Request $request, Response $response): Response {
        if ($err = $this->ensureCompanyUser($request, $response)) {
            return $err;
        }

        $body = $request->getParsedBody() ?: $_POST ?: $_REQUEST;
        $user = $request->getAttribute('user');

        try {
            $company = $this->companyService->createCompany((int)$user->id, $body);
            $response->getBody()->write(json_encode(['data' => $company]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function updateCompany(Request $request, Response $response): Response {
        if ($err = $this->ensureCompanyUser($request, $response)) {
            return $err;
        }

        $body = $request->getParsedBody() ?: $_POST ?: $_REQUEST;
        $user = $request->getAttribute('user');

        try {
            $company = $this->companyService->updateCompanyByUserId((int)$user->id, $body);
            $response->getBody()->write(json_encode(['data' => $company]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}
