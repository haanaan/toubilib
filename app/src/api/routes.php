<?php
declare(strict_types=1);

use Slim\App;
use toubilib\api\actions\{
    ListerPraticiensAction,
    GetPraticienAction,
    GetCreneauxPraticienAction,
    GetRendezVousAction,
    AnnulerRendezVousAction,
    UpdateEtatRendezVousAction,
    CreerRendezVousAction,
    ConsulterAgendaAction,
    HistoriquePatientAction,
    SearchPraticiensAction,
    CreerIndisponibiliteAction
};
use toubilib\api\middlewares\AuthnMiddleware;
use toubilib\api\middlewares\RendezVousAuthzMiddleware;

return function (App $app): void {
    $app->get(
        '/',
        fn($req, $res) =>
        $res->getBody()->write('Bienvenue dans Toubilib API') ? $res : $res
    );

    // Routes praticiens
    $app->get('/praticiens', ListerPraticiensAction::class);
    $app->get('/praticiens/search', SearchPraticiensAction::class);
    $app->get('/praticiens/{id}', GetPraticienAction::class);
    $app->get('/praticiens/{id}/creneaux', GetCreneauxPraticienAction::class)->add(AuthnMiddleware::class);
    $app->get('/praticiens/{id}/agenda', ConsulterAgendaAction::class)
        ->add(RendezVousAuthzMiddleware::class);
    $app->post('/praticiens/{id}/indisponibilites', CreerIndisponibiliteAction::class)
        ->add(AuthnMiddleware::class);

    // Routes rendez-vous
    $app->get('/rendezvous/{id}', GetRendezVousAction::class)
        ->add(RendezVousAuthzMiddleware::class);
    $app->delete('/rendezvous/{id}', AnnulerRendezVousAction::class)->add(AuthnMiddleware::class);
    $app->post('/rendezvous', CreerRendezVousAction::class)
        ->add(RendezVousAuthzMiddleware::class);
    $app->patch('/rdv/{id}/etat', UpdateEtatRendezVousAction::class)->add(AuthnMiddleware::class);

    // Routes patients
    $app->get('/patients/{id}/historique', HistoriquePatientAction::class)
        ->add(AuthnMiddleware::class);
};
