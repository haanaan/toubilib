<?php

declare(strict_types=1);

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\core\application\ports\api\RendezVousServiceInterface;
use toubilib\api\dto\InputRendezVousDTO;

class CreerRendezVousAction
{
    public function __construct(private RendezVousServiceInterface $service)
    {
    }

    public function __invoke(Request $request, Response $response, array $args = []): Response
    {
        $profile = $request->getAttribute('userProfile');

        $dto = $request->getAttribute('inputRendezVousDTO');

        if (!($dto instanceof InputRendezVousDTO)) {
            return $this->json($response, ['error' => "Données d'entrée manquantes ou invalides."], 400);
        }

        try {
            $this->service->creerRendezVous($dto);

            $payload = [
                'message' => 'Rendez-vous créé',
                'id' => $dto->id,
            ];

            $location = '/rendezvous/' . rawurlencode($dto->id);

            $response->getBody()->write((string) json_encode($payload));
            return $response
                ->withStatus(201)
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Location', $location);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $status = 400;
            if (stripos($msg, 'inexistant') !== false) {
                $status = 404;
            } elseif (stripos($msg, 'disponible') !== false || stripos($msg, 'déjà') !== false) {
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