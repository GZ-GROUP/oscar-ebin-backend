<?php

namespace App\Controllers;

use App\Services\ReportService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HistoryController {

    private ReportService $reportService;

    public function __construct() {
        $this->reportService = new ReportService();
    }

    public function history(Request $request, Response $response): Response {
        $user = $request->getAttribute('user');
        $queryParams = $request->getQueryParams();
        $category = $queryParams['category'] ?? null;

        if ($category !== null && !in_array($category, ['session', 'claim'], true)) {
            $response->getBody()->write(json_encode(['error' => 'category debe ser session o claim']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $history = $this->reportService->getUserHistory((int) $user->id, $category);
        $response->getBody()->write(json_encode(['data' => $history]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
