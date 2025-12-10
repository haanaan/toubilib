<?php
declare(strict_types=1);

namespace toubilib\api\provider;

use toubilib\api\dto\AuthTokensDTO;
use toubilib\core\application\exceptions\AuthenticationException;
use toubilib\core\application\usecases\AuthnService;
use toubilib\api\provider\jwt\JWTservice;


class AuthnProvider
{
    private AuthnService $authenticateUser;
    private JWTservice $jwtService;

    public function __construct(AuthnService $authenticateUser, JWTservice $jwtService)
    {
        $this->authenticateUser = $authenticateUser;
        $this->jwtService = $jwtService;
    }

    public function signin(string $email, string $password): AuthTokensDTO
    {
        $profile = $this->authenticateUser->execute($email, $password);


        $accessToken = $this->jwtService->generateAccessToken($profile);
        $refreshToken = $this->jwtService->generateRefreshToken($profile);

        return new AuthTokensDTO(
            $profile,
            $accessToken,
            $refreshToken
        );
    }
}
