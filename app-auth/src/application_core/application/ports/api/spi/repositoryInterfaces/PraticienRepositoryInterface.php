<?php
declare(strict_types=1);

namespace toubilib\core\application\ports\api\spi\repositoryInterfaces;

use toubilib\core\domain\entities\Praticien;

interface PraticienRepositoryInterface
{
    /** @return Praticien[] */
    public function findAll(): array;
    public function findById(string $id): ?Praticien;
}
