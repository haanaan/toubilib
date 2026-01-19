<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use AppRdv\infrastructure\repositories\RendezVousRepository;
use AppRdv\core\application\ports\api\spi\repositoryInterfaces\RendezVousRepositoryInterface;
use AppRdv\core\application\usecases\RendezVousAuthzService;
use AppRdv\core\application\ports\api\RendezVousAuthzServiceInterface;
use AppRdv\api\middlewares\RendezVousAuthzMiddleware;
use PDO;

return [
    'settings' => function () {
        return require __DIR__ . '/settings.php';
    },

    PDO::class => static function (ContainerInterface $c): PDO {
        $settings = $c->get('settings');
        $db = $settings['db_rdv'];
        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $db['host'],
            $db['port'],
            $db['dbname']
        );
        return new PDO($dsn, $db['user'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    },

    RendezVousRepositoryInterface::class => static function (ContainerInterface $c): RendezVousRepositoryInterface {
        return new RendezVousRepository($c->get(PDO::class));
    },

    RendezVousAuthzServiceInterface::class => static function (ContainerInterface $c): RendezVousAuthzServiceInterface {
        return new RendezVousAuthzService($c->get(RendezVousRepositoryInterface::class));
    },

    RendezVousAuthzMiddleware::class => static function (ContainerInterface $c): RendezVousAuthzMiddleware {
        $settings = $c->get('settings');
        return new RendezVousAuthzMiddleware(
            $c->get(RendezVousAuthzServiceInterface::class),
            $settings['jwt']['secret']
        );
    },
];
