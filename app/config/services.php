<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;

use toubilib\core\application\ports\api\spi\repositoryInterfaces\{
    PraticienRepositoryInterface,
    RendezVousRepositoryInterface,
    UserRepositoryInterface
};

use toubilib\infrastructure\repositories\{
    PDOPraticienRepository,
    PDORendezVousRepository
};

use toubilib\core\application\ports\api\{
    PraticienServiceInterface,
    AgendaPraticienServiceInterface,
    RendezVousServiceInterface,
    RendezVousAuthzServiceInterface
};

use toubilib\core\application\usecases\{
    PraticienService,
    RendezVousService,
    AgendaPraticienService,
    AuthnService,
    RendezVousAuthzService,
    RegisterPatientService
};

use toubilib\api\actions\{
    ConsulterAgendaAction,
    SigninAction,
    HistoriquePatientAction,
    RegisterPatientAction

};

use toubilib\infrastructure\repositories\UserRepository;
use toubilib\api\provider\jwt\JwtService;
use toubilib\api\provider\AuthnProvider;
use toubilib\api\middlewares\RendezVousAuthzMiddleware;
use toubilib\api\middlewares\AuthnMiddleware;
use toubilib\api\actions\SearchPraticiensAction;




return [
    // connexions PDO
    'pdo.prat' => static function (ContainerInterface $c): PDO {
        $db = $c->get('settings')['db_prat'];
        $dsn = sprintf('%s:host=%s;port=%d;dbname=%s', $db['driver'], $db['host'], $db['port'], $db['name']);
        return new PDO($dsn, $db['user'], $db['pass'], $db['options']);
    },
    'pdo.rdv' => static function (ContainerInterface $c): PDO {
        $db = $c->get('settings')['db_rdv'];
        $dsn = sprintf('%s:host=%s;port=%d;dbname=%s', $db['driver'], $db['host'], $db['port'], $db['name']);
        return new PDO($dsn, $db['user'], $db['pass'], $db['options']);
    },
    'pdo.pat' => static function (ContainerInterface $c): PDO {
        $db = $c->get('settings')['db_pat'];
        $dsn = sprintf('%s:host=%s;port=%d;dbname=%s', $db['driver'], $db['host'], $db['port'], $db['name']);
        return new PDO($dsn, $db['user'], $db['pass'], $db['options']);
    },
    'pdo.auth' => static function (ContainerInterface $c): PDO {
        $db = $c->get('settings')['db_auth'];
        $dsn = sprintf('%s:host=%s;port=%d;dbname=%s', $db['driver'], $db['host'], $db['port'], $db['name']);
        return new PDO($dsn, $db['user'], $db['pass'], $db['options']);
    },


        // câblage deps
    PraticienRepositoryInterface::class => static fn($c)
        => new PDOPraticienRepository($c->get('pdo.prat')),
    PraticienServiceInterface::class => static fn($c)
        => new PraticienService($c->get(PraticienRepositoryInterface::class)),

    ConsulterAgendaAction::class => static fn($c)
        => new ConsulterAgendaAction(
            $c->get(RendezVousServiceInterface::class)
        ),
    RendezVousRepositoryInterface::class => static fn($c)
        => new PDORendezVousRepository($c->get('pdo.rdv')),
    AgendaPraticienServiceInterface::class => static fn($c)
        => new AgendaPraticienService($c->get(RendezVousRepositoryInterface::class)),

    RendezVousServiceInterface::class => static fn($c)
        => new RendezVousService(
            $c->get(PraticienRepositoryInterface::class),
            $c->get(RendezVousRepositoryInterface::class)
        ),
    UserRepositoryInterface::class => static fn($c)
        => new UserRepository($c->get('pdo.auth')),

    JwtService::class => static function (ContainerInterface $c): JwtService {
        $jwt = $c->get('settings')['jwt'] ?? [];
        $secret = $jwt['secret'] ?? 'change-me-in-env';
        $algo = $jwt['algo'] ?? 'HS256';
        $access = (int) ($jwt['access_ttl'] ?? 3600);
        $refresh = (int) ($jwt['refresh_ttl'] ?? 1209600);
        return new JwtService($secret, $algo, $access, $refresh);
    },

    AuthnService::class => static fn(ContainerInterface $c) =>
        new AuthnService($c->get(UserRepositoryInterface::class)),

    AuthnProvider::class => static fn(ContainerInterface $c) =>
        new AuthnProvider(
            $c->get(AuthnService::class),
            $c->get(JwtService::class)
        ),

    SigninAction::class => static fn($c) => new SigninAction($c->get(AuthnProvider::class)),

    AuthnMiddleware::class => static fn(ContainerInterface $c) =>
        new AuthnMiddleware(
            $c->get(JwtService::class)
        ),

    RendezVousAuthzServiceInterface::class => static fn($c)
        => new RendezVousAuthzService($c->get(RendezVousRepositoryInterface::class)),
    RendezVousAuthzServiceInterface::class => static fn($c)
        => new RendezVousAuthzService(
            $c->get(RendezVousRepositoryInterface::class)
        ),

    RendezVousAuthzMiddleware::class => static fn($c)
        => new RendezVousAuthzMiddleware(
            $c->get(RendezVousAuthzServiceInterface::class)
        ),

    HistoriquePatientAction::class => static fn($c)
        => new HistoriquePatientAction(
            $c->get(RendezVousServiceInterface::class)
        ),
    RegisterPatientService::class => static fn(ContainerInterface $c) =>
        new RegisterPatientService($c->get(UserRepositoryInterface::class)),

    RegisterPatientAction::class => static fn(ContainerInterface $c) =>
        new RegisterPatientAction($c->get(RegisterPatientService::class)),
        
    SearchPraticiensAction::class => static fn($c) =>
    new SearchPraticiensAction(
        $c->get(PraticienServiceInterface::class)
    ),

];
