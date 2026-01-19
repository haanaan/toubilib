<?php
declare(strict_types=1);

namespace AppRdv\core\application\ports\api;

use AppRdv\api\dto\UserProfileDTO;

interface RendezVousAuthzServiceInterface
{
    public function canAccessRendezVous(UserProfileDTO $profile, string $rdvId): bool;

    public function canAccessAgenda(UserProfileDTO $profile, string $praticienId): bool;

    public function canCreateRendezVous(UserProfileDTO $profile, array $data): bool;
}
