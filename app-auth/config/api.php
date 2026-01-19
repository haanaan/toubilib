<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use toubilib\api\actions\{
    ListerPraticiensAction,
    GetPraticienAction,
    GetCreneauxPraticienAction,
    GetRendezVousAction
};
use toubilib\core\application\ports\api\{
    PraticienServiceInterface,
    AgendaPraticienServiceInterface
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
];
