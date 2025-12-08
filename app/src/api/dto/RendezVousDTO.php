<?php
declare(strict_types=1);

namespace toubilib\api\dto;

use toubilib\core\domain\entities\RendezVous;

class RendezVousDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $praticienId,
        public readonly string $debut,
        public readonly string $fin,
        public readonly ?string $motif,
        public readonly ?string $patientId,
        public readonly ?string $patientEmail,
    ) {
    }

    public static function fromEntity(RendezVous $r): self
    {
        return new self(
            $r->getId(),
            $r->getPraticienId(),
            $r->getDebut()->format(DATE_ATOM),
            $r->getFin()->format(DATE_ATOM),
            $r->getMotif(),
            $r->getPatientId(),
            $r->getPatientEmail()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'praticienId' => $this->praticienId,
            'debut' => $this->debut,
            'fin' => $this->fin,
            'motif' => $this->motif,
            'patientId' => $this->patientId,
            'patientEmail' => $this->patientEmail,
        ];
    }
}
