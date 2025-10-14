<?php

namespace toubilib\infrastructure\repositories;

use toubilib\core\domain\entities\praticien\User;
use toubilib\core\domain\entities\praticien\repositories\UserRepositoryInterface;
use PDO;


class UserRepository implements UserRepositoryInterface
{
    private array $users = [
        [
            'id' => 1,
            'email' => 'user@example.com',
            'passwordHash' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'user',
        ],
        [
            'id' => 2,
            'email' => 'admin@example.com',
            'passwordHash' => password_hash('admin123', PASSWORD_BCRYPT),
            'role' => 'admin',
        ],
    ];


    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }



    public function findByEmail(string $email): ?User
    {
        foreach ($this->users as $userData) {
            if ($userData['email'] === $email) {
                return new User(
                    id: $userData['id'],
                    email: $userData['email'],
                    passwordHash: $userData['passwordHash'],
                    role: $userData['role']
                );
            }
        }

        return null;
    }
}