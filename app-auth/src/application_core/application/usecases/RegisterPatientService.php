<?php
declare(strict_types=1);

namespace toubilib\core\application\usecases;

use toubilib\core\application\ports\api\spi\repositoryInterfaces\UserRepositoryInterface;
use toubilib\core\application\exceptions\AuthenticationException;

class RegisterPatientService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function register(string $email, string $password): string
    {
        $existing = $this->userRepository->findByEmail($email);
        if ($existing !== null) {
            throw new AuthenticationException('Un utilisateur existe déjà avec cet email.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        return $this->userRepository->createPatient($email, $hash);
    }
}
