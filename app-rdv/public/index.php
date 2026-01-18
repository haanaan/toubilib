<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();

$app->get('/rdv', function ($request, $response) {
    $host = getenv('DB_HOST') ?: 'toubirdv.db';
    $db = getenv('DB_NAME') ?: 'toubirdv';
    $user = getenv('DB_USER') ?: 'toubirdv';
    $pass = getenv('DB_PASSWORD') ?: 'toubirdv';
    $dsn = "pgsql:host=$host;port=5432;dbname=$db;";
    try {
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->query('SELECT id, praticien_id, patient_id, patient_email, date_heure_debut, status, duree, date_heure_fin, date_creation, motif_visite FROM rdv');
        $rdvs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $rdvs = ['error' => $e->getMessage()];
    }
    $response->getBody()->write(json_encode($rdvs));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
