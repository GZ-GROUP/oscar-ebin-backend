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
        $transactionType = $queryParams['type'] ?? null;
        $page = max(1, (int) ($queryParams['page'] ?? 1));
        $limit = max(1, min(100, (int) ($queryParams['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;

        if ($category !== null && !in_array($category, ['session', 'claim'], true)) {
            $response->getBody()->write(json_encode(['error' => 'category debe ser session o claim']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if ($transactionType !== null && !in_array($transactionType, ['credit', 'debit'], true)) {
            $response->getBody()->write(json_encode(['error' => 'type debe ser credit o debit']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $history = $this->reportService->getUserHistory((int) $user->id, $category, $transactionType, $limit, $offset);
        $response->getBody()->write(json_encode([
            'data' => $history,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'category' => $category,
                'type' => $transactionType,
            ],
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
