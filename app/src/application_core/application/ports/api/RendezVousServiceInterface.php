<?php

declare(strict_types=1);

namespace toubilib\core\application\ports\api;

use toubilib\api\dto\InputRendezVousDTO;

interface RendezVousServiceInterface
{
    public function creerRendezVous(InputRendezVousDTO $dto): void;
    public function annulerRendezVous(string $rdvId, ?string $raison = null): void;

    /**
     * @return array
     */
    public function consulterAgenda(string $praticienId, ?\DateTimeImmutable $from = null, ?\DateTimeImmutable $to = null): array;
    public function historiquePatient(string $patientId): array;

}