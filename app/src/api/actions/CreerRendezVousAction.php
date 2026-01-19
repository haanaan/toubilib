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
        try {
            $profile = $request->getAttribute('userProfile');
            $data = $request->getParsedBody();

            if (!$data || !is_array($data)) {
                return $this->json($response, ['error' => "Données d'entrée manquantes ou invalides.", 'received' => $data], 400);
            }

            // Créer le DTO depuis les données reçues
            // Calculer la fin si non fournie (début + durée)
            $debut = $data['date_heure_debut'] ?? '';
            $duree = $data['duree'] ?? 30;
            $fin = $data['date_heure_fin'] ?? null;
            
            if (!$fin && $debut) {
                $debutDateTime = new \DateTime($debut);
                $debutDateTime->add(new \DateInterval('PT' . $duree . 'M'));
                $fin = $debutDateTime->format('Y-m-d H:i:s');
            }
            
            $dto = new InputRendezVousDTO(
                id: $data['id'] ?? \Ramsey\Uuid\Uuid::uuid4()->toString(),
                praticienId: $data['praticien_id'] ?? '',
                debut: $debut,
                fin: $fin ?? '',
                motif: $data['motif_visite'] ?? '',
                patientId: $data['patient_id'] ?? '',
                patientEmail: $data['patient_email'] ?? ''
            );

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
            $trace = $e->getTraceAsString();
            $status = 400;
            if (stripos($msg, 'inexistant') !== false) {
                $status = 404;
            } elseif (stripos($msg, 'disponible') !== false || stripos($msg, 'déjà') !== false) {
                $status = 409;
            }

            return $this->json($response, ['error' => $msg, 'trace' => $trace], $status);
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