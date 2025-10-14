<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\UserProfileDTO;
use toubilib\core\domain\entities\praticien\repositories\UserRepositoryInterface;
use toubilib\core\domain\entities\exceptions\AuthenticationException;

class AuthenticateUser
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function execute(string $email, string $password): UserProfileDTO
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        return new UserProfileDTO(
            id: $user->getId(),
            email: $user->getEmail(),
            role: $user->getRole()
        );
    }
}