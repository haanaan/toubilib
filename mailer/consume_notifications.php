<?php


use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

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

    $transport = Transport::fromDsn('smtp://mail.toubi:1025');
    $mailer = new Mailer($transport);

    $email = (new Email())
        ->from('noreply@toubilib.local')
        ->to($data['email'] ?? 'test@toubilib.local')
        ->subject($data['subject'] ?? 'Notification RDV')
        ->text($data['message'] ?? 'Vous avez reçu une notification RDV.');

    try {
        $mailer->send($email);
        echo "Mail envoyé à : " . $email->getTo()[0]->getAddress() . "\n";
    } catch (Exception $e) {
        echo "Erreur lors de l'envoi du mail : " . $e->getMessage() . "\n";
    }
};

$channel->basic_consume($queue, '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
