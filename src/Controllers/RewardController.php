<?php

namespace App\Controllers;

use App\Services\RewardService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RewardController {

    private RewardService $rewardService;

    public function __construct() {
        $this->rewardService = new RewardService();
    }

    public function redeem(Request $request, Response $response): Response {
        $user = $request->getAttribute('user');
        $body = $request->getParsedBody();

        $rewardId = isset($body['reward_id']) ? (int) $body['reward_id'] : null;

        if (!$rewardId) {
            $response->getBody()->write(json_encode(['error' => 'reward_id es requerido']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $claim = $this->rewardService->redeemReward((int) $user->id, $rewardId);
            $response->getBody()->write(json_encode(['data' => $claim]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}
