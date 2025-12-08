<?php
declare(strict_types=1);

namespace toubilib\core\application\usecases;

use toubilib\api\dto\PraticienDTO;
use toubilib\core\application\ports\api\spi\repositoryInterfaces\PraticienRepositoryInterface;
use toubilib\core\application\ports\api\PraticienServiceInterface;

class ServicePraticien implements PraticienServiceInterface
{
    public function __construct(private PraticienRepositoryInterface $repo)
    {
    }
    public function listerPraticiens(): array
    {
        return array_map(fn($p) => PraticienDTO::fromEntity($p), $this->repo->findAll());
    }
    public function getPraticien(string $id): ?PraticienDTO
    {
        $p = $this->repo->findById($id);
        return $p ? PraticienDTO::fromEntity($p) : null;
    }
}
