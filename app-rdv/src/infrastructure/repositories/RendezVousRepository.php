<?php
declare(strict_types=1);

namespace AppRdv\infrastructure\repositories;

use AppRdv\core\application\ports\api\spi\repositoryInterfaces\RendezVousRepositoryInterface;
use PDO;

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
}
