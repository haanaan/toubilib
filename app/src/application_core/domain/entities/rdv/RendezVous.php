<?php
declare(strict_types=1);

namespace toubilib\core\domain\entities\rdv;

use DomainException;

class RendezVous
{
    public function __construct(
        private string $id,
        private string $praticienId,
        private \DateTimeImmutable $debut,
        private \DateTimeImmutable $fin,
        private ?string $motif = null,
        private ?string $patientId = null,
        private ?string $patientEmail = null,
        private string $etat = 'prevu',
        private ?\DateTimeImmutable $dateAnnulation = null,
        private ?string $raisonAnnulation = null,

    ) {
    }
    public function getId(): string
    {
        return $this->id;
    }
    public function getPraticienId(): string
    {
        return $this->praticienId;
    }
    public function getDebut(): \DateTimeImmutable
    {
        return $this->debut;
    }
    public function getFin(): \DateTimeImmutable
    {
        return $this->fin;
    }
    public function getMotif(): ?string
    {
        return $this->motif;
    }
    public function getPatientId(): ?string
    {
        return $this->patientId;
    }
    public function getPatientEmail(): ?string
    {
        return $this->patientEmail;
    }
    public function getEtat(): string
    {
        return $this->etat;
    }
    public function getDateAnnulation(): ?\DateTimeImmutable
    {
        return $this->dateAnnulation;
    }
    public function getRaisonAnnulation(): ?string
    {
        return $this->raisonAnnulation;
    }
    public function annuler(?string $raison = null): void
    {
        if ($this->etat === 'annule') {
            throw new DomainException('Rendez-vous deja annule.');
        }

        if ($this->debut <= new \DateTimeImmutable()) {
            throw new DomainException('Impossible d\'annuler un rendez-vous passe ou en cours.');
        }

        $this->etat = 'annule';
        $this->dateAnnulation = new \DateTimeImmutable();
        $this->raisonAnnulation = $raison;
    }
}
