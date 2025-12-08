<?php

namespace toubilib\api\dto;

class UserProfileDTO
{
    public function __construct(
        public int $id,
        public string $email,
        public string $role
    ) {
    }
}