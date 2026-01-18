<?php
declare(strict_types=1);

use Slim\App;
use gateway\api\actions\ListerPraticiensAction;
use gateway\api\actions\GetRdvPraticienAction;
use gateway\api\actions\GetPraticienByIdAction;

return function (App $app): void {
    $app->get('/', function ($req, $res) {
        $res->getBody()->write('Bienvenue dans Toubilib Gateway');
        return $res;
    });

    $app->get('/praticiens', ListerPraticiensAction::class);
    $app->get('/praticiens/{id}', GetPraticienByIdAction::class);
    $app->get('/praticiens/{id}/agenda', GetRdvPraticienAction::class);
};