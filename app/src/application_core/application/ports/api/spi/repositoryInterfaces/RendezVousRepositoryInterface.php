<?php
declare(strict_types=1);

namespace toubilib\core\application\ports\api\spi\repositoryInterfaces;

use toubilib\core\domain\entities\RendezVous;

interface RendezVousRepositoryInterface
{
    public function findById(string $id): ?RendezVous;
    /** @return RendezVous[] */
    public function findBusyForPraticienBetween(string $praticienId, \DateTimeImmutable $from, \DateTimeImmutable $to): array;

    public function save(RendezVous $rdv): void;

    /** @return RendezVous[] */
    public function findForPraticienBetween(string $praticienId, \DateTimeImmutable $from, \DateTimeImmutable $to): array;

}
