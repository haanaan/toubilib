<?php

declare(strict_types=1);

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\api\dto\UserProfileDTO;
use toubilib\core\application\ports\api\RendezVousServiceInterface;

final class HistoriquePatientAction
{
    public function __construct(private RendezVousServiceInterface $service)
    {
    }

    public function __invoke(Request $request, Response $response, array $args = []): Response
    {
        /** @var UserProfileDTO|null $profile */
        $profile = $request->getAttribute('userProfile');

        $patientId = $args['id'] ?? null;
        if ($patientId === null) {
            return $this->json($response, ['error' => 'Identifiant patient manquant'], 400);
        }

        if ($profile === null || $profile->role !== 'patient' || (string) $profile->id !== (string) $patientId) {
            return $this->json($response, ['error' => 'Forbidden'], 403);
        }

        $data = $this->service->historiquePatient((string) $patientId);

        $response->getBody()->write(json_encode($data));
        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json');
    }

    private function json(Response $response, array $data, int $status): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }
}
