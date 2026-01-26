<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;

require_once __DIR__ . '/vendor/autoload.php';

$queue = 'rdv_notifications';

$connection = new AMQPStreamConnection('rabbitmq', 5672, 'toubi', 'toubi');
$channel = $connection->channel();

$channel->queue_declare($queue, false, true, false, false);

echo "En attente de messages dans la queue '{$queue}'...\n";

$callback = function ($msg) {
    $data = json_decode($msg->body, true);
    echo "Message reçu :\n";
    print_r($data);
    echo "\n-----------------------------\n";
};

$channel->basic_consume($queue, '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
