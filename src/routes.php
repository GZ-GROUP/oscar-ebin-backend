<?php

use Slim\App;
use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;

return function (App $app) {

    $app->group('/api', function ($group) {

        $group->post('/login', AuthController::class . ':login');

        $group->post('/signup', AuthController::class . ':signup');

        $group->get('/profile', function ($request, $response) {

            $user = $request->getAttribute('user');

            $response->getBody()->write(json_encode($user));

            return $response->withHeader('Content-Type', 'application/json');

        })->add(new AuthMiddleware());

    });

};