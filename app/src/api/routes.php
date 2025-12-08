<?php
declare(strict_types=1);

use Slim\App;
use toubilib\api\actions\{
    ListerPraticiensAction,
    GetPraticienAction,
    GetCreneauxPraticienAction,
    GetRendezVousAction,
    AnnulerRendezVousAction,
    ConsulterAgendaAction,
    SigninAction,
};
use toubilib\api\provider\AuthnProvider;
use toubilib\core\domain\entities\exceptions\AuthenticationException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

return function (App $app): void {
    $app->get(
        '/',
        fn($req, $res) =>
        $res->getBody()->write('Bienvenue dans Toubilib API') ? $res : $res
    );

    $app->get('/praticiens', ListerPraticiensAction::class);
    $app->get('/praticiens/{id}', GetPraticienAction::class);
    $app->get('/praticiens/{id}/creneaux', GetCreneauxPraticienAction::class);
    $app->get('/rendezvous/{id}', GetRendezVousAction::class);
    $app->delete('/rendezvous/{id}', AnnulerRendezVousAction::class);
    $app->get('/praticiens/{id}/agenda', ConsulterAgendaAction::class);
    $app->post('/signin', function ($request, $response) use ($app) {
        $params = (array) $request->getParsedBody();
        $email = $params['email'] ?? '';
        $password = $params['password'] ?? '';

        /** @var AuthnProvider $authProvider */
        $authProvider = $app->getContainer()->get(AuthnProvider::class);

        try {
            $authTokensDTO = $authProvider->signin($email, $password);
            $payload = json_encode($authTokensDTO);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $ex) {
            $error = ['error' => $ex->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    });




};
