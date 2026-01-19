<?php
declare(strict_types=1);

use Slim\App;
use toubilib\api\actions\{
    SigninAction,
    RefreshAction,
    RegisterPatientAction,
    ValidateTokenAction
};

return function (App $app): void {
    $app->get(
        '/',
        fn($req, $res) =>
        $res->getBody()->write('Service d\'authentification Toubilib') ? $res : $res
    );

    // Routes d'authentification
    $app->post('/auth/signin', SigninAction::class);
    $app->post('/auth/refresh', RefreshAction::class);
    $app->post('/patients/register', RegisterPatientAction::class);
    
    // Validation de token
    $app->post('/tokens/validate', ValidateTokenAction::class);
};
