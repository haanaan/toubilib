<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;

use toubilib\core\domain\entities\praticien\repositories\PraticienRepositoryInterface;
use toubilib\infrastructure\repositories\PDOPraticienRepository;
use toubilib\core\application\usecases\ServicePraticien;
use toubilib\core\application\usecases\ServicePraticienInterface;

use toubilib\core\domain\entities\rdv\repositories\RendezVousRepositoryInterface;
use toubilib\infrastructure\repositories\PDORendezVousRepository;
use toubilib\core\application\usecases\AgendaPraticien;
use toubilib\core\application\usecases\AgendaPraticienInterface;
use toubilib\core\application\usecases\ServiceRendezVous;
use toubilib\core\application\usecases\ServiceRendezVousInterface;

use toubilib\core\domain\entities\praticien\repositories\UserRepositoryInterface;
use toubilib\infrastructure\repositories\UserRepository;
use toubilib\core\application\usecases\AuthenticateUser;


return [
    // 3 connexions PDO
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

        // câblage deps
    PraticienRepositoryInterface::class => static fn($c)
        => new PDOPraticienRepository($c->get('pdo.prat')),
    ServicePraticienInterface::class => static fn($c)
        => new ServicePraticien($c->get(PraticienRepositoryInterface::class)),

    RendezVousRepositoryInterface::class => static fn($c)
        => new PDORendezVousRepository($c->get('pdo.rdv')),
    AgendaPraticienInterface::class => static fn($c)
        => new AgendaPraticien($c->get(RendezVousRepositoryInterface::class)),

    ServiceRendezVousInterface::class => static fn($c)
        => new ServiceRendezVous(
            $c->get(PraticienRepositoryInterface::class),
            $c->get(RendezVousRepositoryInterface::class)
        ),
    UserRepositoryInterface::class => static fn($c)
        => new UserRepository($c->get('pdo.pat')),

    AuthenticateUser::class => static fn($c)
        => new AuthenticateUser($c->get(UserRepositoryInterface::class)),
];
