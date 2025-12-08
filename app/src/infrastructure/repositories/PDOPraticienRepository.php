<?php
declare(strict_types=1);

namespace toubilib\infrastructure\repositories;

use toubilib\core\domain\entities\Praticien;
use toubilib\core\application\ports\api\spi\repositoryInterfaces\PraticienRepositoryInterface;

class PDOPraticienRepository implements PraticienRepositoryInterface
{
    public function __construct(private \PDO $pdo)
    {
    }

    /**
     * @return Praticien[]
     */
    public function findAll(): array
    {
        $sql = "
            SELECT p.id,
                   p.nom,
                   p.prenom,
                   p.ville,
                   p.email,
                   s.libelle AS specialite,
                   mv.libelle AS motif
            FROM public.praticien p
            JOIN public.specialite s ON s.id = p.specialite_id
            LEFT JOIN public.praticien2motif p2m ON p2m.praticien_id = p.id
            LEFT JOIN public.motif_visite mv ON mv.id = p2m.motif_id
            ORDER BY p.nom, p.prenom
        ";

        $rows = $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        $grouped = [];

        foreach ($rows as $r) {
            $id = (string) $r['id'];
            if (!isset($grouped[$id])) {
                $grouped[$id] = [
                    'id' => $id,
                    'nom' => $r['nom'] ?? '',
                    'prenom' => $r['prenom'] ?? '',
                    'ville' => $r['ville'] ?? '',
                    'email' => $r['email'] ?? '',
                    'specialite' => $r['specialite'] ?? '',
                    'motifs' => [],
                ];
            }
            if (!empty($r['motif'])) {
                $grouped[$id]['motifs'][] = $r['motif'];
            }
        }

        $result = [];
        foreach ($grouped as $g) {
            $motifs = array_values(array_unique($g['motifs']));
            $result[] = new Praticien(
                $g['id'],
                $g['nom'],
                $g['prenom'],
                $g['ville'],
                $g['email'],
                $g['specialite'],
                $motifs
            );
        }

        return $result;
    }

    public function findById(string $id): ?Praticien
    {
        $sql = "
            SELECT p.id,
                   p.nom,
                   p.prenom,
                   p.ville,
                   p.email,
                   s.libelle AS specialite,
                   p.specialite_id,
                   mv.libelle AS motif
            FROM public.praticien p
            JOIN public.specialite s ON s.id = p.specialite_id
            LEFT JOIN public.praticien2motif p2m ON p2m.praticien_id = p.id
            LEFT JOIN public.motif_visite mv ON mv.id = p2m.motif_id
            WHERE p.id = :id
        ";

        $st = $this->pdo->prepare($sql);
        $st->execute([':id' => $id]);
        $rows = $st->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return null;
        }

        $motifs = [];
        $specialiteId = null;
        foreach ($rows as $r) {
            if ($specialiteId === null && isset($r['specialite_id'])) {
                $specialiteId = $r['specialite_id'];
            }
            if (!empty($r['motif'])) {
                $motifs[] = $r['motif'];
            }
        }
        $motifs = array_values(array_unique($motifs));

        if (empty($motifs) && $specialiteId !== null) {
            $sql2 = "SELECT libelle FROM public.motif_visite WHERE specialite_id = :sid";
            $st2 = $this->pdo->prepare($sql2);
            $st2->execute([':sid' => $specialiteId]);
            $rows2 = $st2->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows2 as $r2) {
                if (!empty($r2['libelle'])) {
                    $motifs[] = $r2['libelle'];
                }
            }
            $motifs = array_values(array_unique($motifs));
        }

        $first = $rows[0];

        return new Praticien(
            (string) $first['id'],
            $first['nom'] ?? '',
            $first['prenom'] ?? '',
            $first['ville'] ?? '',
            $first['email'] ?? '',
            $first['specialite'] ?? '',
            $motifs
        );
    }
}
