<?php
declare(strict_types=1);

use Slim\App;
use gateway\api\actions\ProxyApiAction;
use gateway\api\actions\RegisterAction;
use gateway\api\actions\SigninAction;
use gateway\api\actions\RefreshAction;
use gateway\api\middlewares\AuthMiddleware;

return function (App $app): void {
    // Routes d'authentification
    $app->post('/auth/register', RegisterAction::class);
    $app->post('/auth/signin', SigninAction::class);
    $app->post('/auth/refresh', RefreshAction::class);
    
    // Routes RDV protégées par authentification
    $app->get('/rendezvous/{id}', ProxyApiAction::class)->add(AuthMiddleware::class);
    $app->post('/rendezvous', ProxyApiAction::class)->add(AuthMiddleware::class);
    $app->get('/praticiens/{id}/agenda', ProxyApiAction::class)->add(AuthMiddleware::class);
    
    // Route proxy pour toutes les autres requêtes
    $app->any('/{routes:.+}', ProxyApiAction::class);
};