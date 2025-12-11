<?php
declare(strict_types=1);

namespace toubilib\api\actions;

use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\api\dto\UserProfileDTO;

class CreerIndisponibiliteAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $praticienId = $args['id'] ?? null;

        /** @var UserProfileDTO|null $userProfile */
        $userProfile = $request->getAttribute('userProfile');

        if ($userProfile === null || $userProfile->role !== 'praticien') {
            $response->getBody()->write(json_encode([
                'error' => 'Accès interdit : rôle praticien requis',
            ]));
            return $response
                ->withStatus(403)
                ->withHeader('Content-Type', 'application/json');
        }

        if ((string) $userProfile->id !== (string) $praticienId) {
            $response->getBody()->write(json_encode([
                'error' => 'Accès interdit : praticien non autorisé pour cette opération',
            ]));
            return $response
                ->withStatus(403)
                ->withHeader('Content-Type', 'application/json');
        }

        $body = (string) $request->getBody();
        $data = json_decode($body, true);

        if (!is_array($data) || !isset($data['debut'], $data['fin'])) {
            $response->getBody()->write(json_encode([
                'error' => 'Champs "debut" et "fin" obligatoires',
            ]));
            return $response
                ->withStatus(400)
                ->withHeader('Content-Type', 'application/json');
        }

        try {
            $debut = new DateTimeImmutable($data['debut']);
            $fin = new DateTimeImmutable($data['fin']);
        } catch (\Exception) {
            $response->getBody()->write(json_encode([
                'error' => 'Format de date invalide (attendu : ISO 8601)',
            ]));
            return $response
                ->withStatus(400)
                ->withHeader('Content-Type', 'application/json');
        }

        if ($fin <= $debut) {
            $response->getBody()->write(json_encode([
                'error' => '"fin" doit être strictement après "debut"',
            ]));
            return $response
                ->withStatus(400)
                ->withHeader('Content-Type', 'application/json');
        }

        // pas de persistance en base, on loggue juste l'indisponibilité
        error_log(sprintf(
            '[Indisponibilite] Praticien %s indisponible de %s à %s (non persisté en base)',
            $praticienId,
            $debut->format(DATE_ATOM),
            $fin->format(DATE_ATOM)
        ));

        $payload = [
            'message' => 'Indisponibilité enregistrée (non persistée en base de données)',
            'praticien_id' => $praticienId,
            'debut' => $debut->format(DATE_ATOM),
            'fin' => $fin->format(DATE_ATOM),
            '_links' => [
                'praticien' => ['href' => "/praticiens/{$praticienId}"],
            ],
        ];

        $response->getBody()->write(json_encode($payload));

        return $response
            ->withStatus(201)
            ->withHeader('Content-Type', 'application/json');
    }
}
