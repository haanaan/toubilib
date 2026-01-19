<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;
use gateway\api\actions\ProxyApiAction;
use gateway\api\actions\RegisterAction;
use gateway\api\actions\SigninAction;
use gateway\api\actions\RefreshAction;
use gateway\api\middlewares\AuthMiddleware;

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

    RegisterAction::class => static function (ContainerInterface $c): RegisterAction {
        $settings = $c->get('settings');
        return new RegisterAction(
            $c->get(Client::class),
            $settings['app_auth']['base_uri']
        );
    },

    SigninAction::class => static function (ContainerInterface $c): SigninAction {
        $settings = $c->get('settings');
        return new SigninAction(
            $c->get(Client::class),
            $settings['app_auth']['base_uri']
        );
    },

    RefreshAction::class => static function (ContainerInterface $c): RefreshAction {
        $settings = $c->get('settings');
        return new RefreshAction(
            $c->get(Client::class),
            $settings['app_auth']['base_uri']
        );
    },

    AuthMiddleware::class => static function (ContainerInterface $c): AuthMiddleware {
        $settings = $c->get('settings');
        // Créer un client spécifique pour le middleware pointant vers app-auth
        $authClient = new Client([
            'base_uri' => $settings['app_auth']['base_uri'],
            'timeout' => 5
        ]);
        return new AuthMiddleware($authClient);
    },
];
