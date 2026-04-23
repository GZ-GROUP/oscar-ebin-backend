<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Slim\Factory\AppFactory;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// Rutas
(require __DIR__ . '/../src/routes.php')($app);

$app->run();