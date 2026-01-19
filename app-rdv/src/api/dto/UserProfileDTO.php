<?php
declare(strict_types=1);

namespace AppRdv\api\dto;

class UserProfileDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly string $role
    ) {
    }
}
