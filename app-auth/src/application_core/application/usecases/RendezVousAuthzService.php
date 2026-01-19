<?php
declare(strict_types=1);

namespace toubilib\core\application\usecases;

use toubilib\api\dto\UserProfileDTO;
use toubilib\core\application\ports\api\RendezVousAuthzServiceInterface;
use toubilib\core\application\ports\api\spi\repositoryInterfaces\RendezVousRepositoryInterface;

class RendezVousAuthzService implements RendezVousAuthzServiceInterface
{
    public function __construct(private RendezVousRepositoryInterface $rdvRepository)
    {
    }

    public function canAccessRendezVous(UserProfileDTO $profile, string $rdvId): bool
    {
        $rdv = $this->rdvRepository->findById($rdvId);
        if ($rdv === null) {
            return false;
        }

        $role = $profile->role;
        $userId = (string) $profile->id;

        if ($role === 'praticien') {
            return $rdv->getPraticienId() === $userId;
        }

        if ($role === 'patient') {
            return $rdv->getPatientId() === $userId;
        }

        return false;
    }

    public function canAccessAgenda(UserProfileDTO $profile, string $praticienId): bool
    {
        if ($profile->role !== 'praticien') {
            return false;
        }

        $userId = (string) $profile->id;

        return $userId === $praticienId;
    }
}
