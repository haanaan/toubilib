<?php

declare(strict_types=1);

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface;
use toubilib\core\application\ports\api\RendezVousServiceInterface;

final class AnnulerRendezVousAction
{
    private RendezVousServiceInterface $service;

    public function __construct(ContainerInterface $container)
    {
        $this->service = $container->get(RendezVousServiceInterface::class);
    }

    public function __invoke(Request $request, Response $response, array $args = []): Response
    {
        $id = $args['id'] ?? null;
        if (empty($id)) {
            return $this->json($response, ['error' => "Identifiant du rendez-vous manquant."], 400);
        }

        $body = $request->getParsedBody();
        $raison = null;
        if (is_array($body) && isset($body['raison'])) {
            $raison = trim((string) $body['raison']);
        }

        try {
            $this->service->annulerRendezVous((string) $id, $raison);
            return $response->withStatus(204);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $status = 400;
            if (stripos($msg, 'inexistant') !== false) {
                $status = 404;
            } elseif (
                stripos($msg, 'deja') !== false ||
                stripos($msg, 'annul') !== false ||
                stripos($msg, 'Impossible') !== false
            ) {
                $status = 409;
            }

            return $this->json($response, ['error' => $msg], $status);
        }
    }

    private function json(Response $response, array $data, int $status): Response
    {
        $response->getBody()->write((string) json_encode($data));
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }
}

use function DI\autowire;

return [
    \PDO::class => function () {
        $dsn = 'pgsql:host=localhost;port=5432;dbname=toubiprat'; // <- adapter
        $user = 'votre_user';
        $pass = 'votre_pass';
        $pdo = new \PDO($dsn, $user, $pass, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    },

    \toubilib\core\application\ports\api\spi\repositoryInterfaces\RendezVousRepositoryInterface::class
    => autowire(\toubilib\infrastructure\repositories\PDORendezVousRepository::class),

    \toubilib\core\application\ports\api\spi\repositoryInterfaces\PraticienRepositoryInterface::class
    => autowire(\toubilib\infrastructure\repositories\PDOPraticienRepository::class),

    RendezVousServiceInterface::class => autowire(\toubilib\core\application\usecases\ServiceRendezVous::class),

];