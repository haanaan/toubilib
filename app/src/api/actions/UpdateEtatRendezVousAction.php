<?php
declare(strict_types=1);

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\core\application\usecases\RendezVousService;

class UpdateEtatRendezVousAction
{
    private RendezVousService $service;

    public function __construct(RendezVousService $service)
    {
        $this->service = $service;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (string)$args['id'];
        $body = $request->getParsedBody();
        $etat = $body['etat'] ?? null;

        if (!$etat) {
            $response->getBody()->write(json_encode([
                "error" => "Le champ 'etat' est obligatoire"
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $this->service->changerEtatRendezVous($id, $etat);

            $response->getBody()->write(json_encode([
                "message" => "État mis à jour avec succès",
                "id" => $id,
                "etat" => $etat
            ]));

            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                "error" => $e->getMessage()
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }
}
