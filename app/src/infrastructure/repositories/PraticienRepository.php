<?php
namespace toubilib\infrastructure\repositories;

use PDO;
use toubilib\core\domain\entities\Praticien;
use toubilib\core\application\ports\api\spi\repositoryInterfaces\PraticienRepositoryInterface;

class PraticienRepository implements PraticienRepositoryInterface
{

    public function findById(string $id): ?Praticien
    {
        $sql = "SELECT p.id,
                       p.nom,
                       p.prenom,
                       p.ville,
                       p.email,
                       s.libelle AS specialite
                FROM praticien p
                JOIN specialite s ON s.id = p.id_specialite
                WHERE p.id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new Praticien(
                (int) $row['id'],
                (string) $row['nom'],
                (string) $row['prenom'],
                (string) $row['ville'],
                (string) $row['email'],
                (string) $row['specialite'],
                (array) $row['motifs'],
            );
        }

        return null;
    }
    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $sql = "SELECT p.id,
                       p.nom,
                       p.prenom,
                       p.ville,
                       p.email,
                       s.libelle AS specialite
                FROM praticien p
                JOIN specialite s ON s.id = p.id_specialite
                ORDER BY p.nom, p.prenom";

        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $list = [];
        foreach ($rows as $r) {
            $list[] = new Praticien(
                (int) $r['id'],
                (string) $r['nom'],
                (string) $r['prenom'],
                (string) $r['ville'],
                (string) $r['email'],
                (string) $r['specialite'],
                (array) $r['motifs']
            );
        }
        return $list;
    }
}
