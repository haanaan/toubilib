<?php
declare(strict_types=1);

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\UserProfileDTO;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    public function __construct(
        private string $secret,
        private string $algo = 'HS256',
        private int $accessTtl = 3600,       // 1h
        private int $refreshTtl = 1209600    // 14j
    ) {
    }

    public function generateAccessToken(UserProfileDTO $profile): string
    {
        $now = time();
        $payload = [
            'iss' => 'toubilib.api',
            'sub' => $profile->id,
            'email' => $profile->email,
            'role' => $profile->role,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->accessTtl,
            'type' => 'access',
        ];
        return JWT::encode($payload, $this->secret, $this->algo);
    }

    public function generateRefreshToken(UserProfileDTO $profile): string
    {
        $now = time();
        $payload = [
            'iss' => 'toubilib.api',
            'sub' => $profile->id,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->refreshTtl,
            'type' => 'refresh',
        ];
        return JWT::encode($payload, $this->secret, $this->algo);
    }

    public function verify(string $jwt): object
    {
        return JWT::decode($jwt, new Key($this->secret, $this->algo));
    }
}
