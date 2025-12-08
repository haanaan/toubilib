<?php
declare(strict_types=1);

namespace toubilib\core\application\ports\api;

use toubilib\api\dto\PraticienDTO;

interface PraticienServiceInterface
{
    /** @return PraticienDTO[] */
    public function listerPraticiens(): array;
    public function getPraticien(string $id): ?PraticienDTO;
}
