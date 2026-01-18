<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;
use gateway\api\actions\ListerPraticiensAction;
use gateway\api\actions\GetRdvPraticienAction;
use gateway\api\actions\GetPraticienByIdAction;

return [

    Client::class => static function (ContainerInterface $c): Client {
        $cfg = $c->get('settings')['toubilib_api'];
        return new Client([
            'base_uri' => $cfg['base_uri'],
            'timeout' => $cfg['timeout'],
        ]);
    },

    ListerPraticiensAction::class => static function (ContainerInterface $c): ListerPraticiensAction {
        return new ListerPraticiensAction($c->get(Client::class));
    },

    GetPraticienByIdAction::class => static function (ContainerInterface $c): GetPraticienByIdAction {
        return new GetPraticienByIdAction($c->get(Client::class), $c->get('settings')['toubilib_api']['base_uri']);
    },

    GetRdvPraticienAction::class => static function (ContainerInterface $c): GetRdvPraticienAction {
        return new GetRdvPraticienAction($c->get(Client::class), $c->get('settings')['toubilib_api']['base_uri']);
    },
];
