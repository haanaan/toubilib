<?php
declare(strict_types=1);

use Slim\App;
use gateway\api\actions\ListerPraticiensAction;

return function (App $app): void {
    $app->get('/', function ($req, $res) {
        $res->getBody()->write('Bienvenue dans Toubilib Gateway');
        return $res;
    });

    $app->get('/praticiens', ListerPraticiensAction::class);
};