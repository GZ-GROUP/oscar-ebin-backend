<?php

namespace App\Controllers;

use App\Services\ReportService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RankingController {

    private ReportService $reportService;

    public function __construct() {
        $this->reportService = new ReportService();
    }

    public function ranking(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        $page = max(1, (int) ($queryParams['page'] ?? 1));
        $limit = max(1, min(100, (int) ($queryParams['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;

        $results = $this->reportService->getRanking($limit, $offset);
        $response->getBody()->write(json_encode([
            'data' => $results,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
            ],
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
