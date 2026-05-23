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
        $results = $this->reportService->getRanking();
        $response->getBody()->write(json_encode(['data' => $results]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
