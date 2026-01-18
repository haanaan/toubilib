<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;
use gateway\api\actions\ProxyApiAction;

return [

    Client::class => static function (ContainerInterface $c): Client {
        $cfg = $c->get('settings')['toubilib_api'];
        return new Client([
            'base_uri' => $cfg['base_uri'],
            'timeout' => $cfg['timeout'],
        ]);
    },

    ProxyApiAction::class => static function (ContainerInterface $c): ProxyApiAction {
        return new ProxyApiAction($c->get(Client::class), $c->get('settings')['toubilib_api']['base_uri']);
    },
];
