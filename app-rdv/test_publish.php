<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

require_once __DIR__ . '/vendor/autoload.php';

$exchange_name = 'toubilib_events';
$routing_key = 'rdv.create';

$connection = new AMQPStreamConnection('rabbitmq', 5672, 'toubi', 'toubi');
$channel = $connection->channel();

$msg_body = [
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

$msg = new AMQPMessage(json_encode($msg_body));
$channel->basic_publish($msg, $exchange_name, $routing_key);

echo "[x] Message publié:\n";
echo json_encode($msg_body, JSON_PRETTY_PRINT) . "\n";

$channel->close();
$connection->close();
