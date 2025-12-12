<?php
declare(strict_types=1);

namespace toubilib\infrastructure\repositories;

use toubilib\core\domain\entities\RendezVous;
use toubilib\core\application\ports\api\spi\repositoryInterfaces\RendezVousRepositoryInterface;
use toubilib\core\application\dto\InputRendezVousDTO;

class PDORendezVousRepository implements RendezVousRepositoryInterface
{
    public function __construct(private \PDO $pdo)
    {
    }

    private array $etatToStatus = [
        'prevu' => 0,
        'confirme' => 1,
        'annule' => 2,
    ];

    private array $statusToEtat = [
        0 => 'prevu',
        1 => 'confirme',
        2 => 'annule',
    ];

    public function findById(string $id): ?RendezVous
    {
        $sql = "SELECT id, praticien_id, patient_id, patient_email,
                       date_heure_debut, date_heure_fin, motif_visite, status
                FROM public.rdv
                WHERE id = :id";
        $st = $this->pdo->prepare($sql);
        $st->execute([':id' => $id]);
        $r = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$r)
            return null;

        $etat = $this->statusToEtat[(int) $r['status']] ?? 'prevu';

        return new RendezVous(
            (string) $r['id'],
            (string) $r['praticien_id'],
            new \DateTimeImmutable($r['date_heure_debut']),
            new \DateTimeImmutable($r['date_heure_fin']),
            $r['motif_visite'] ?? null,
            $r['patient_id'] ?? null,
            $r['patient_email'] ?? null,
            $etat,
            isset($r['date_annulation']) ? new \DateTimeImmutable($r['date_annulation']) : null,
            $r['raison_annulation'] ?? null
        );
    }

    public function findBusyForPraticienBetween(string $praticienId, \DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $sql = "SELECT id, praticien_id, patient_id, patient_email,
                       date_heure_debut, date_heure_fin, motif_visite
                FROM public.rdv
                WHERE praticien_id = :pid
                  AND date_heure_debut < :to
                  AND date_heure_fin   > :from
                ORDER BY date_heure_debut ASC";
        $st = $this->pdo->prepare($sql);
        $st->execute([
            ':pid' => $praticienId,
            ':from' => $from->format('Y-m-d H:i:s'),
            ':to' => $to->format('Y-m-d H:i:s'),
        ]);
        $rows = $st->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($r) => new RendezVous(
            (string) $r['id'],
            (string) $r['praticien_id'],
            new \DateTimeImmutable($r['date_heure_debut']),
            new \DateTimeImmutable($r['date_heure_fin']),
            $r['motif_visite'] ?? null,
            $r['patient_id'] ?? null,
            $r['patient_email'] ?? null,
        ), $rows);
    }

    public function save(RendezVous $rdv): void
    {
        $status = $this->etatToStatus[$rdv->getEtat()] ?? 0;

        $sql = "UPDATE public.rdv
                SET status = :status,
                    motif_visite = :motif,
                    patient_id = :patient_id,
                    patient_email = :patient_email,
                    date_heure_debut = :debut,
                    date_heure_fin = :fin
                WHERE id = :id";

        $st = $this->pdo->prepare($sql);
        $st->execute([
            ':status' => $status,
            ':motif' => $rdv->getMotif(),
            ':patient_id' => $rdv->getPatientId(),
            ':patient_email' => $rdv->getPatientEmail(),
            ':debut' => $rdv->getDebut()->format('Y-m-d H:i:s'),
            ':fin' => $rdv->getFin()->format('Y-m-d H:i:s'),
            ':id' => $rdv->getId(),
        ]);

        if ($rdv->getEtat() === 'annule') {
            $sql2 = "UPDATE public.rdv
                     SET motif_visite = :motif,
                         status = :status,
                         date_heure_fin = :fin
                     WHERE id = :id";
            $st2 = $this->pdo->prepare($sql2);
            $st2->execute([
                ':status' => $status,
                ':motif' => $rdv->getMotif(),
                ':fin' => $rdv->getFin()->format('Y-m-d H:i:s'),
                ':id' => $rdv->getId(),
            ]);
        }
    }

    public function findForPraticienBetween(string $praticienId, \DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $sql = "SELECT * FROM rdv
        WHERE praticien_id = :praticienId
          AND date_heure_debut >= :from
          AND date_heure_fin <= :to
        ORDER BY date_heure_debut";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':praticienId' => $praticienId,
            ':from' => $from->format('Y-m-d H:i:s'),
            ':to' => $to->format('Y-m-d H:i:s'),
        ]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($r) => new RendezVous(
            $r['id'],
            $r['praticien_id'],
            new \DateTimeImmutable($r['date_heure_debut']),
            new \DateTimeImmutable($r['date_heure_fin']),
            $r['motif_visite'] ?? null,
            $r['patient_id'] ?? null,
            $r['patient_email'] ?? null,
            (int) ($r['status'] ?? 0) === 2 ? 'annule' : ((int) ($r['status'] ?? 0) === 1 ? 'confirme' : 'prevu'),
            isset($r['date_annulation']) ? new \DateTimeImmutable($r['date_annulation']) : null,
            $r['raison_annulation'] ?? null
        ), $rows);
    }

    public function findForPatient(string $patientId): array
    {
        $sql = "SELECT id, praticien_id, patient_id, patient_email,
                   date_heure_debut, date_heure_fin, motif_visite, status
            FROM public.rdv
            WHERE patient_id = :pid
            ORDER BY date_heure_debut DESC";
        $st = $this->pdo->prepare($sql);
        $st->execute([':pid' => $patientId]);
        $rows = $st->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($r) => new RendezVous(
            (string) $r['id'],
            (string) $r['praticien_id'],
            new \DateTimeImmutable($r['date_heure_debut']),
            new \DateTimeImmutable($r['date_heure_fin']),
            $r['motif_visite'] ?? null,
            $r['patient_id'] ?? null,
            $r['patient_email'] ?? null,
            $this->statusToEtat[(int) ($r['status'] ?? 0)] ?? 'prevu',
            isset($r['date_annulation']) ? new \DateTimeImmutable($r['date_annulation']) : null,
            $r['raison_annulation'] ?? null
        ), $rows);
    }

public function updateEtat(string $id, string $etat)
{
    $sql = "UPDATE rendezvous SET etat = :etat WHERE id = :id";
    $st = $this->pdo->prepare($sql);
    return $st->execute([
        ':etat' => $etat,
        ':id'   => $id
                                ]);
    }

}
