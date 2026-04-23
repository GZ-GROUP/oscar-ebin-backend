<?php

use Slim\App;
use App\Controllers\AuthController;

return function (App $app) {
    $app->post('/signup', AuthController::class . ':signup');
    $app->post('/login', AuthController::class . ':login');
};