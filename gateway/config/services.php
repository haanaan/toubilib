<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;

return [

    Client::class => static function (ContainerInterface $c): Client {
        $cfg = $c->get('settings')['toubilib_api'];
        return new Client([
            'base_uri' => $cfg['base_uri'],
            'timeout' => $cfg['timeout'],
        ]);
    },

];
