<?php

namespace toubilib\core\domain\entities;

class User
{
    public function __construct(
        private string $id,
        private string $email,
        private string $passwordHash,
        private string $role
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }
}