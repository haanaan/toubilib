<?php
declare(strict_types=1);

namespace AppRdv\infrastructure\repositories;

use AppRdv\core\application\ports\api\spi\repositoryInterfaces\RendezVousRepositoryInterface;
use PDO;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RendezVousRepository implements RendezVousRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(string $id): ?object
    {
        $stmt = $this->pdo->prepare('SELECT * FROM rdv WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        return $result ?: null;
    }

    public function save(array $rdv): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO rdv (id, praticien_id, patient_id, date_heure_debut, date_heure_fin, motif_visite, status) VALUES (:id, :praticien_id, :patient_id, :debut, :fin, :motif, :status)');
        $stmt->execute([
            ':id' => $rdv['id'],
            ':praticien_id' => $rdv['praticien_id'],
            ':patient_id' => $rdv['patient_id'],
            ':debut' => $rdv['date_heure_debut'],
            ':fin' => $rdv['date_heure_fin'],
            ':motif' => $rdv['motif_visite'] ?? null,
            ':status' => $rdv['status'] ?? 0
        ]);

        $this->publishRdvCreatedMessage($rdv);
    }

    private function publishRdvCreatedMessage(array $rdv): void
    {
        $exchange = 'toubilib_events';
        $routingKey = 'rdv.create';

        $connection = new AMQPStreamConnection('rabbitmq', '5672', 'toubi', 'toubi');
        $channel = $connection->channel();

        $msgBody = [
            'event_type' => 'CREATE',
            'rdv_id' => $rdv['id'],
            'praticien_id' => $rdv['praticien_id'],
            'patient_id' => $rdv['patient_id'],
            'date_heure' => $rdv['date_heure_debut'],
            'duree' => $rdv['duree'] ?? null,
            'destinataires' => [
                ['type' => 'praticien', 'id' => $rdv['praticien_id']],
                ['type' => 'patient', 'id' => $rdv['patient_id']]
            ]
        ];

        $msg = new AMQPMessage(json_encode($msgBody));
        $channel->basic_publish($msg, $exchange, $routingKey);

        $channel->close();
        $connection->close();
    }
}
