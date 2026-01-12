<?php
declare(strict_types=1);

use Slim\App;
return function (App $app): void {
    $app->get(
        '/',
        fn($req, $res) => $res->getBody()->write('Bienvenue dans Toubilib Gateway') and $res
    );
};