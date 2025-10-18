<?php
declare(strict_types=1);

namespace toubilib\core\application\dto;

class AuthTokensDTO
{
    public function __construct(
        public UserProfileDTO $profile,
        public string $accessToken,
        public string $refreshToken
    ) {
    }
}
