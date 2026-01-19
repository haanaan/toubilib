<?php
declare(strict_types=1);

namespace toubilib\api\dto;

class AuthTokensDTO
{
    public function __construct(
        public UserProfileDTO $profile,
        public string $accessToken,
        public string $refreshToken
    ) {
    }
}
