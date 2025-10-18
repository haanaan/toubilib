<?php
declare(strict_types=1);

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\AuthTokensDTO;
use toubilib\core\domain\entities\exceptions\AuthenticationException;


class AuthenticationProvider
{
    private AuthenticateUser $authenticateUser;
    private JWTservice $jwtService;

    public function __construct(AuthenticateUser $authenticateUser, JWTservice $jwtService)
    {
        $this->authenticateUser = $authenticateUser;
        $this->jwtService = $jwtService;
    }

    public function signin(string $email, string $password): AuthTokensDTO
    {
        $profile = $this->authenticateUser->execute($email, $password);

        if ($profile === null) {
            throw new AuthenticationException("Identifiants invalides.");
        }

        $accessToken = $this->jwtService->generateAccessToken($profile);
        $refreshToken = $this->jwtService->generateRefreshToken($profile);

        return new AuthTokensDTO(
            $profile,
            $accessToken,
            $refreshToken
        );
    }
}
