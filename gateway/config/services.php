<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;
use gateway\api\actions\ProxyApiAction;

return [
    Client::class => static function (ContainerInterface $c): Client {
        return new Client(['timeout' => 5]);
    },

    ProxyApiAction::class => static function (ContainerInterface $c): ProxyApiAction {
        $settings = $c->get('settings');
        return new ProxyApiAction(
            $c->get(Client::class),
            $settings['toubilib_api']['base_uri'],
            $settings['app_praticiens']['base_uri'],
            $settings['app_rdv']['base_uri']
        );
    },
];
