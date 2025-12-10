<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\core\application\ports\api\RendezVousServiceInterface;

class ConsulterAgendaAction
{
    public function __construct(private RendezVousServiceInterface $service)
    {
    }

    public function __invoke(Request $request, Response $response, array $args = []): Response
    {
        $profile = $request->getAttribute('userProfile');

        $praticienId = $args['praticienId'] ?? $args['id'] ?? null;
        if (!$praticienId) {
            return $this->json($response, ['error' => 'praticienId manquant'], 400);
        }

        $query = $request->getQueryParams();
        $from = isset($query['from']) ? new \DateTimeImmutable($query['from']) : null;
        $to = isset($query['to']) ? new \DateTimeImmutable($query['to']) : null;

        try {
            $agenda = $this->service->consulterAgenda($praticienId, $from, $to);
            return $this->json($response, $agenda, 200);
        } catch (\Exception $e) {
            return $this->json($response, ['error' => $e->getMessage()], 500);
        }
    }

    private function json(Response $response, array $data, int $status): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }
}
