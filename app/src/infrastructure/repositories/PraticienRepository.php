<?php
namespace toubilib\infrastructure\repositories;

use PDO;
use toubilib\core\domain\entities\Praticien;
use toubilib\core\application\ports\api\spi\repositoryInterfaces\PraticienRepositoryInterface;

class PraticienRepository implements PraticienRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    private function mapRowToEntity(array $row): Praticien
    {
        // PostgreSQL renvoie déjà un tableau pour array_agg
        $motifs = $row['motifs'] ?? [];
        if (!is_array($motifs)) {
            $motifs = [];
        }

        return new Praticien(
            (string) $row['id'],
            (string) $row['nom'],
            (string) $row['prenom'],
            (string) $row['ville'],
            (string) $row['email'],
            (string) $row['specialite'],
            $motifs
        );
    }

    public function findById(string $id): ?Praticien
    {
        $sql = "
            SELECT 
                p.id,
                p.nom,
                p.prenom,
                p.ville,
                p.email,
                s.libelle AS specialite,
                ARRAY(
                    SELECT m.libelle
                    FROM praticien2motif pm
                    JOIN motif_visite m ON m.id = pm.motif_id
                    WHERE pm.praticien_id = p.id
                ) AS motifs
            FROM praticien p
            JOIN specialite s ON s.id = p.specialite_id
            WHERE p.id = :id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToEntity($row) : null;
    }

    public function findAll(): array
    {
        $sql = "
            SELECT 
                p.id,
                p.nom,
                p.prenom,
                p.ville,
                p.email,
                s.libelle AS specialite,
                ARRAY(
                    SELECT m.libelle
                    FROM praticien2motif pm
                    JOIN motif_visite m ON m.id = pm.motif_id
                    WHERE pm.praticien_id = p.id
                ) AS motifs
            FROM praticien p
            JOIN specialite s ON s.id = p.specialite_id
            ORDER BY p.nom, p.prenom
        ";

        $rows = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($r) => $this->mapRowToEntity($r), $rows);
    }

    public function findByFilters(?string $ville, ?string $specialite): array
    {
        $sql = "
            SELECT 
                p.id,
                p.nom,
                p.prenom,
                p.ville,
                p.email,
                s.libelle AS specialite,
                ARRAY(
                    SELECT m.libelle
                    FROM praticien2motif pm
                    JOIN motif_visite m ON m.id = pm.motif_id
                    WHERE pm.praticien_id = p.id
                ) AS motifs
            FROM praticien p
            JOIN specialite s ON s.id = p.specialite_id
            WHERE 1=1
        ";

        $params = [];

        if ($ville) {
            $sql .= " AND p.ville ILIKE :ville";
            $params['ville'] = $ville;
        }

        if ($specialite) {
            $sql .= " AND s.libelle ILIKE :specialite";
            $params['specialite'] = $specialite;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($r) => $this->mapRowToEntity($r), $rows);
    }
}
