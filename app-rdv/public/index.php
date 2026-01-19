<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

// Créer le conteneur DI
$container = new Container();

// Charger les services
$services = require __DIR__ . '/../config/services.php';
foreach ($services as $key => $service) {
    $container->set($key, $service);
}

// Créer l'application Slim avec le conteneur
AppFactory::setContainer($container);
$app = AppFactory::create();

// Ajouter le middleware de parsing du body
$app->addBodyParsingMiddleware();

// Ajouter le middleware de routing
$app->addRoutingMiddleware();

// Ajouter le middleware de gestion d'erreurs
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Charger les routes
$routes = require __DIR__ . '/../config/routes.php';
$routes($app);

$app->run();
