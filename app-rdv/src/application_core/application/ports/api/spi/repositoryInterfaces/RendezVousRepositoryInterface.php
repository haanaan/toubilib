<?php
declare(strict_types=1);

namespace AppRdv\core\application\ports\api\spi\repositoryInterfaces;

interface RendezVousRepositoryInterface
{
    public function findById(string $id): ?object;
}
