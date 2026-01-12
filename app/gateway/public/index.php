<?php
use Slim\Factory\AppFactory;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);


(require __DIR__ . '/../src/dependencies.php')($app);
(require __DIR__ . '/../src/cors.php')($app);
(require __DIR__ . '/../src/routes.php')($app);

$app->run();
