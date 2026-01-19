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

    public function refresh(string $refreshToken): AuthTokensDTO
    {
        try {
            $payload = $this->jwtService->verify($refreshToken);
            
            // Vérifier que c'est bien un refresh token
            if (!isset($payload->type) || $payload->type !== 'refresh') {
                throw new AuthenticationException('Token invalide');
            }

            // Récupérer le profil utilisateur depuis le repository
            $userProfile = $this->authenticateUser->getUserProfileById($payload->sub);
            
            if (!$userProfile) {
                throw new AuthenticationException('Utilisateur non trouvé');
            }

            // Générer de nouveaux tokens
            $newAccessToken = $this->jwtService->generateAccessToken($userProfile);
            $newRefreshToken = $this->jwtService->generateRefreshToken($userProfile);

            return new AuthTokensDTO(
                $userProfile,
                $newAccessToken,
                $newRefreshToken
            );
        } catch (\Exception $e) {
            throw new AuthenticationException('Refresh token invalide ou expiré');
        }
    }
}
