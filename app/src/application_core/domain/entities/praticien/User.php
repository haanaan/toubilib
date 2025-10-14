<?php

namespace toubilib\core\domain\entities\praticien;

class User
{
    public function __construct(
        private int $id,
        private string $email,
        private string $passwordHash,
        private string $role
    ) {
    }

    public function getId(): int
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