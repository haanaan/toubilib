<?php

declare(strict_types=1);

namespace toubilib\core\application\usecases;

use toubilib\api\dto\InputRendezVousDTO;
use toubilib\core\domain\entities\RendezVous;
use toubilib\core\application\ports\api\RendezVousServiceInterface;
use toubilib\core\application\ports\api\spi\repositoryInterfaces\PraticienRepositoryInterface;
use toubilib\core\application\ports\api\spi\repositoryInterfaces\RendezVousRepositoryInterface;
use Exception;

class ServiceRendezVous implements RendezVousServiceInterface
{
    public function __construct(
        private PraticienRepositoryInterface $praticienRepository,
        private RendezVousRepositoryInterface $rdvRepository
    ) {
    }

    public function creerRendezVous(InputRendezVousDTO $dto): void
    {
        $praticien = $this->praticienRepository->findById($dto->praticienId);
        if (!$praticien) {
            throw new Exception("Praticien inexistant.");
        }

        if ($dto->motif && !in_array($dto->motif, $praticien->getMotifs())) {
            throw new Exception('Motif non autorisé pour ce praticien');
        }

        $debut = new \DateTimeImmutable($dto->debut);
        $fin = new \DateTimeImmutable($dto->fin);

        $jourSemaine = (int) $debut->format('N');
        $heureDebut = (int) $debut->format('H');
        $heureFin = (int) $fin->format('H');

        if ($jourSemaine > 5) {
            throw new Exception("Le rendez-vous doit être pris un jour ouvré (lundi à vendredi).");
        }
        if ($heureDebut < 8 || $heureFin > 19 || $fin <= $debut) {
            throw new Exception("L'horaire du rendez-vous doit être entre 8h et 19h, et la fin après le début.");
        }

        $rdvsOccupes = $this->rdvRepository->findBusyForPraticienBetween(
            $dto->praticienId,
            $debut,
            $fin
        );
        if (count($rdvsOccupes) > 0) {
            throw new Exception("Le praticien n'est pas disponible sur ce créneau.");
        }

        $rdv = new RendezVous(
            $dto->id,
            $dto->praticienId,
            $debut,
            $fin,
            $dto->motif,
            $dto->patientId,
            $dto->patientEmail
        );

        $this->rdvRepository->save($rdv);
    }
    public function annulerRendezVous(string $rdvId, ?string $raison = null): void
    {
        $id = (string) $rdvId;

        $rdv = $this->rdvRepository->findById($id);
        if (!$rdv) {
            throw new Exception('Rendez-vous inexistant.');
        }

        try {
            $rdv->annuler($raison);
        } catch (\DomainException $e) {
            throw new Exception($e->getMessage());
        }

        $this->rdvRepository->save($rdv);
    }

    public function consulterAgenda(string $praticienId, ?\DateTimeImmutable $from = null, ?\DateTimeImmutable $to = null): array
    {
        $from ??= new \DateTimeImmutable('today 00:00:00');
        $to ??= new \DateTimeImmutable('today 23:59:59');

        $rdvs = $this->rdvRepository->findForPraticienBetween($praticienId, $from, $to);

        return array_map(fn(RendezVous $r) => [
            'id' => $r->getId(),
            'debut' => $r->getDebut()->format(DATE_ATOM),
            'fin' => $r->getFin()->format(DATE_ATOM),
            'motif' => $r->getMotif(),
            'etat' => $r->getEtat(),
            'patient' => $r->getPatientId() ? [
                'id' => $r->getPatientId(),
                'email' => $r->getPatientEmail(),
                'link' => '/patients/' . rawurlencode($r->getPatientId())
            ] : null
        ], $rdvs);
    }

}