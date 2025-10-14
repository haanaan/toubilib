<?php

namespace toubilib\core\application\dto;

class UserProfileDTO
{
    public function __construct(
        public int $id,
        public string $email,
        public string $role
    ) {
    }
}