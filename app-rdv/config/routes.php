<?php
declare(strict_types=1);

use Slim\App;
use AppRdv\api\middlewares\RendezVousAuthzMiddleware;

return function (App $app): void {
    // Routes rendez-vous avec autorisation
    $app->get('/rendezvous/{id}', function ($request, $response, $args) {
        // Action pour récupérer un RDV
        $pdo = $this->get(PDO::class);
        $stmt = $pdo->prepare('SELECT * FROM rdv WHERE id = :id');
        $stmt->execute(['id' => $args['id']]);
        $rdv = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$rdv) {
            $response->getBody()->write(json_encode(['error' => 'Rendez-vous not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        $response->getBody()->write(json_encode($rdv));
        return $response->withHeader('Content-Type', 'application/json');
    })->add(RendezVousAuthzMiddleware::class);

    $app->post('/rendezvous', function ($request, $response) {
        // Action pour créer un RDV
        $data = json_decode((string) $request->getBody(), true);
        $pdo = $this->get(PDO::class);
        
        $stmt = $pdo->prepare('INSERT INTO rdv (praticien_id, patient_id, patient_email, date_heure_debut, duree, status, motif_visite) VALUES (:praticien_id, :patient_id, :patient_email, :date_heure_debut, :duree, :status, :motif_visite) RETURNING *');
        $stmt->execute([
            'praticien_id' => $data['praticien_id'] ?? null,
            'patient_id' => $data['patient_id'] ?? null,
            'patient_email' => $data['patient_email'] ?? null,
            'date_heure_debut' => $data['date_heure_debut'] ?? null,
            'duree' => $data['duree'] ?? 30,
            'status' => 'planifie',
            'motif_visite' => $data['motif_visite'] ?? null
        ]);
        $rdv = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $response->getBody()->write(json_encode($rdv));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    })->add(RendezVousAuthzMiddleware::class);

    $app->get('/praticiens/{id}/agenda', function ($request, $response, $args) {
        // Action pour récupérer l'agenda d'un praticien
        $pdo = $this->get(PDO::class);
        $stmt = $pdo->prepare('SELECT * FROM rdv WHERE praticien_id = :praticien_id ORDER BY date_heure_debut');
        $stmt->execute(['praticien_id' => $args['id']]);
        $rdvs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response->getBody()->write(json_encode($rdvs));
        return $response->withHeader('Content-Type', 'application/json');
    })->add(RendezVousAuthzMiddleware::class);

    // Route de santé (health check)
    $app->get('/health', function ($request, $response) {
        $response->getBody()->write(json_encode(['status' => 'ok', 'service' => 'app-rdv']));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
