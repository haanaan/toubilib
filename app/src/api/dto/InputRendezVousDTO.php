<?php

declare(strict_types=1);

namespace toubilib\api\dto;

class InputRendezVousDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $praticienId,
        public readonly string $debut,
        public readonly string $fin,
        public readonly string $motif,
        public readonly string $patientId,
        public readonly string $patientEmail,
    ) {
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

