<?php
declare(strict_types=1);

namespace AppRdv\core\application\usecases;

use AppRdv\api\dto\UserProfileDTO;
use AppRdv\core\application\ports\api\RendezVousAuthzServiceInterface;
use AppRdv\core\application\ports\api\spi\repositoryInterfaces\RendezVousRepositoryInterface;

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
            return $rdv->praticien_id === $userId;
        }

        if ($role === 'patient') {
            return $rdv->patient_id === $userId;
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

    public function canCreateRendezVous(UserProfileDTO $profile, array $data): bool
    {
        $role = $profile->role;
        $userId = (string) $profile->id;

        // Un praticien peut créer un RDV pour lui-même
        if ($role === 'praticien') {
            return isset($data['praticien_id']) && $data['praticien_id'] === $userId;
        }

        // Un patient peut créer un RDV pour lui-même
        if ($role === 'patient') {
            return isset($data['patient_id']) && $data['patient_id'] === $userId;
        }

        return false;
    }
}
