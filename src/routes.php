<?php

use Slim\App;
use App\Controllers\AuthController;

return function (App $app) {
    $app->group('/api', function ($group) {
        $group->post('/login', AuthController::class . ':login');
        $group->post('/signup', AuthController::class . ':signup');
    });
};