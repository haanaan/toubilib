<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();


$app->get('/praticiens', function ($request, $response) {
    $host = getenv('DB_HOST') ?: 'toubiprati.db';
    $db = getenv('DB_NAME') ?: 'toubiprat';
    $user = getenv('DB_USER') ?: 'toubiprat';
    $pass = getenv('DB_PASSWORD') ?: 'toubiprat';
    $dsn = "pgsql:host=$host;port=5432;dbname=$db;";
    try {
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->query('SELECT id, nom, prenom, ville, email, telephone, specialite_id, titre FROM praticien');
        $praticiens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $praticiens = ['error' => $e->getMessage()];
    }
    $response->getBody()->write(json_encode($praticiens));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/praticiens/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $host = getenv('DB_HOST') ?: 'toubiprati.db';
    $db = getenv('DB_NAME') ?: 'toubiprat';
    $user = getenv('DB_USER') ?: 'toubiprat';
    $pass = getenv('DB_PASSWORD') ?: 'toubiprat';
    $dsn = "pgsql:host=$host;port=5432;dbname=$db;";
    try {
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->prepare('SELECT id, nom, prenom, ville, email, telephone, specialite_id, titre FROM praticien WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $praticien = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$praticien) {
            $response->getBody()->write(json_encode(['error' => 'Praticien non trouvé']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
    $response->getBody()->write(json_encode($praticien));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/praticiens/{id}/agenda', function ($request, $response, $args) {
    $id = $args['id'];
    $host = getenv('RDV_DB_HOST') ?: 'toubirdv.db';
    $db = getenv('RDV_DB_NAME') ?: 'toubirdv';
    $user = getenv('RDV_DB_USER') ?: 'toubirdv';
    $pass = getenv('RDV_DB_PASSWORD') ?: 'toubirdv';
    $dsn = "pgsql:host=$host;port=5432;dbname=$db;";
    try {
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->prepare('SELECT id, praticien_id, patient_id, patient_email, date_heure_debut, status, duree, date_heure_fin, date_creation, motif_visite FROM rdv WHERE praticien_id = :id ORDER BY date_heure_debut');
        $stmt->execute([':id' => $id]);
        $agenda = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
    $response->getBody()->write(json_encode($agenda));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
