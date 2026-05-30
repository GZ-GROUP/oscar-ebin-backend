<?php

use Slim\App;
use App\Controllers\AuthController;
use App\Controllers\OscarController;
use App\Controllers\RankingController;
use App\Controllers\RewardController;
use App\Controllers\HistoryController;
use App\Controllers\ProfileController;
use App\Middleware\AuthMiddleware;

return function (App $app) {

    $app->group('/api', function ($group) {

        $group->post('/login', AuthController::class . ':login');
        $group->post('/signup', AuthController::class . ':signup');
        $group->get('/ranking', RankingController::class . ':ranking');
        $group->get('/oscar/location', OscarController::class . ':locations');
        $group->post('/oscar/item', OscarController::class . ':createItem');

        $group->group('', function ($group) {
            $group->post('/oscar/claim', OscarController::class . ':claim');
            $group->post('/redeem', RewardController::class . ':redeem');
            $group->get('/history', HistoryController::class . ':history');
            $group->get('/profile', ProfileController::class . ':profile');
        })->add(new AuthMiddleware());

    });

};