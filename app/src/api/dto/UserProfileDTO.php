<?php

namespace toubilib\api\dto;

class UserProfileDTO
{
    public function __construct(
        public string $id,
        public string $email,
        public string $role
    ) {
    }
}