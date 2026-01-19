<?php

namespace toubilib\core\application\usecases;

use toubilib\api\dto\UserProfileDTO;
use toubilib\core\application\ports\api\spi\repositoryInterfaces\UserRepositoryInterface;
use toubilib\core\application\exceptions\AuthenticationException;

class AuthnService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function execute(string $email, string $password): UserProfileDTO
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            throw new AuthenticationException('Identifiants invalides.');
        }

        return new UserProfileDTO(
            id: $user->getId(),
            email: $user->getEmail(),
            role: $user->getRole()
        );
    }

    public function getUserProfileById(string $id): ?UserProfileDTO
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            return null;
        }

        return new UserProfileDTO(
            id: $user->getId(),
            email: $user->getEmail(),
            role: $user->getRole()
        );
    }
}