<?php

namespace App\Controllers;

use App\Services\ReportService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProfileController {

    private ReportService $reportService;

    public function __construct() {
        $this->reportService = new ReportService();
    }

    public function profile(Request $request, Response $response): Response {
        $user = $request->getAttribute('user');

        try {
            $metrics = $this->reportService->getUserProfileMetrics((int) $user->id);
            $response->getBody()->write(json_encode(['data' => $metrics]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}
