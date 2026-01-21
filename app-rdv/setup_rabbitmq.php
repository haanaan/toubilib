<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;

require_once __DIR__ . '/vendor/autoload.php';

$exchange_name = 'toubilib_events';
$queue_name = 'rdv_notifications';
$routing_key = 'rdv.create';

$connection = new AMQPStreamConnection('rabbitmq', 5672, 'toubi', 'toubi');
$channel = $connection->channel();

$channel->exchange_declare($exchange_name, 'direct', false, true, false);
$channel->queue_declare($queue_name, false, true, false, false);
$channel->queue_bind($queue_name, $exchange_name, $routing_key);

echo "RabbitMQ setup complete:\n";
echo "- Exchange: $exchange_name\n";
echo "- Queue: $queue_name\n";
echo "- Routing Key: $routing_key\n";

$channel->close();
$connection->close();
