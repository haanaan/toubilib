<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;
use gateway\api\actions\ListerPraticiensAction;

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

];
