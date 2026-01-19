<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use toubilib\api\actions\{
    ListerPraticiensAction,
    GetPraticienAction,
    GetCreneauxPraticienAction,
    GetRendezVousAction,
    CreerRendezVousAction,
    AnnulerRendezVousAction,
    UpdateEtatRendezVousAction,
    ConsulterAgendaAction,
    HistoriquePatientAction,
    SearchPraticiensAction,
    CreerIndisponibiliteAction
};
use toubilib\core\application\ports\api\{
    PraticienServiceInterface,
    AgendaPraticienServiceInterface,
    RendezVousServiceInterface
};

return [
    ListerPraticiensAction::class => static fn(ContainerInterface $c)
        => new ListerPraticiensAction(
            $c->get(PraticienServiceInterface::class),
            $c->get(AgendaPraticienServiceInterface::class)
        ),

    GetPraticienAction::class => static fn(ContainerInterface $c)
        => new GetPraticienAction($c->get(PraticienServiceInterface::class)),

    GetCreneauxPraticienAction::class => static fn(ContainerInterface $c)
        => new GetCreneauxPraticienAction($c->get(AgendaPraticienServiceInterface::class)),

    GetRendezVousAction::class => static fn(ContainerInterface $c)
        => new GetRendezVousAction($c->get(AgendaPraticienServiceInterface::class)),

    CreerRendezVousAction::class => static fn(ContainerInterface $c)
        => new CreerRendezVousAction($c->get(RendezVousServiceInterface::class)),
];
