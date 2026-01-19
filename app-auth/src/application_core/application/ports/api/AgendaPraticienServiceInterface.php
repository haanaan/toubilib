<?php
declare(strict_types=1);

namespace toubilib\core\application\ports\api;

use toubilib\api\dto\RendezVousDTO;

interface AgendaPraticienServiceInterface
{
    /** @return RendezVousDTO[] */
    public function getCreneauxOccupes(string $praticienId, \DateTimeImmutable $from, \DateTimeImmutable $to): array;
    public function getRendezVous(string $id): ?RendezVousDTO;
    public function getProchainRdv(string $praticienId, \DateTimeImmutable $from, \DateTimeImmutable $to): ?RendezVousDTO;
}
