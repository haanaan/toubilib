<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

require_once __DIR__ . '/vendor/autoload.php';

$exchange_name = 'toubilib_events';

$connection = new AMQPStreamConnection('rabbitmq', 5672, 'toubi', 'toubi');
$channel = $connection->channel();

$msg_body_create = [
    'event_type' => 'CREATE',
    'rdv_id' => 'test-' . uniqid(),
    'praticien_id' => '592692c8-4a8c-3f91-967b-fde67ebea54d',
    'patient_id' => 'd975aca7-50c5-3d16-b211-cf7d302cba50',
    'date_heure' => '2026-02-15 10:00:00',
    'duree' => 30,
    'destinataires' => [
        ['type' => 'praticien', 'id' => '592692c8-4a8c-3f91-967b-fde67ebea54d'],
        ['type' => 'patient', 'id' => 'd975aca7-50c5-3d16-b211-cf7d302cba50']
    ]
];
$msg_create = new AMQPMessage(json_encode($msg_body_create));
$channel->basic_publish($msg_create, $exchange_name, 'rdv.create');
echo "[x] Message CREATE publié:\n";
echo json_encode($msg_body_create, JSON_PRETTY_PRINT) . "\n";

$msg_body_cancel = [
    'event_type' => 'CANCEL',
    'rdv_id' => 'test-' . uniqid(),
    'praticien_id' => '592692c8-4a8c-3f91-967b-fde67ebea54d',
    'patient_id' => 'd975aca7-50c5-3d16-b211-cf7d302cba50',
    'date_heure' => '2026-02-15 10:00:00',
    'duree' => 30,
    'raison_annulation' => 'Patient indisponible',
    'destinataires' => [
        ['type' => 'praticien', 'id' => '592692c8-4a8c-3f91-967b-fde67ebea54d'],
        ['type' => 'patient', 'id' => 'd975aca7-50c5-3d16-b211-cf7d302cba50']
    ]
];
$msg_cancel = new AMQPMessage(json_encode($msg_body_cancel));
$channel->basic_publish($msg_cancel, $exchange_name, 'rdv.cancel');
echo "[x] Message CANCEL publié:\n";
echo json_encode($msg_body_cancel, JSON_PRETTY_PRINT) . "\n";

$channel->close();
$connection->close();
