<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();

$app->get('/praticiens', function ($request, $response) {
    $praticiens = [
    ];
    $response->getBody()->write(json_encode($praticiens));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
