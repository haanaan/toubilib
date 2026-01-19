<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;

use toubilib\core\application\ports\api\spi\repositoryInterfaces\{
    UserRepositoryInterface
};

use toubilib\core\application\usecases\{
    AuthnService,
    RegisterPatientService
};

use toubilib\api\actions\{
    SigninAction,
    RefreshAction,
    RegisterPatientAction,
    ValidateTokenAction
};

use toubilib\infrastructure\repositories\UserRepository;
use toubilib\api\provider\jwt\JwtService;
use toubilib\api\provider\AuthnProvider;

return [
    // Connexion PDO pour l'authentification
    'pdo.auth' => static function (ContainerInterface $c): PDO {
        $db = $c->get('settings')['db_auth'];
        $dsn = sprintf('%s:host=%s;port=%d;dbname=%s', $db['driver'], $db['host'], $db['port'], $db['name']);
        return new PDO($dsn, $db['user'], $db['pass'], $db['options']);
    },

    // Repositories
    UserRepositoryInterface::class => static fn($c)
        => new UserRepository($c->get('pdo.auth')),

    // Services
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

    RegisterPatientService::class => static fn(ContainerInterface $c) =>
        new RegisterPatientService($c->get(UserRepositoryInterface::class)),

    // Actions
    SigninAction::class => static fn($c) => new SigninAction($c->get(AuthnProvider::class)),

    RefreshAction::class => static fn($c) => new RefreshAction($c->get(AuthnProvider::class)),

    RegisterPatientAction::class => static fn(ContainerInterface $c) =>
        new RegisterPatientAction($c->get(RegisterPatientService::class)),

    ValidateTokenAction::class => static fn(ContainerInterface $c) =>
        new ValidateTokenAction($c->get(JwtService::class)),
];
