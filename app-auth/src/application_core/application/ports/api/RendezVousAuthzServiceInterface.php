<?php
declare(strict_types=1);

namespace toubilib\core\application\ports\api;

use toubilib\api\dto\UserProfileDTO;

interface RendezVousAuthzServiceInterface
{
    public function canAccessRendezVous(UserProfileDTO $profile, string $rdvId): bool;

    public function canAccessAgenda(UserProfileDTO $profile, string $praticienId): bool;
}
