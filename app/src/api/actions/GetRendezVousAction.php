<?php
declare(strict_types=1);

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\ports\api\AgendaPraticienServiceInterface;

class GetRendezVousAction
{
    public function __construct(private AgendaPraticienServiceInterface $agenda)
    {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (string) $args['id'] ?? null;
        $dto = $this->agenda->getRendezVous($id);

        if (!$dto) {
            $response->getBody()->write(json_encode(['error' => 'RDV not found'], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode($dto->toArray(), JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
