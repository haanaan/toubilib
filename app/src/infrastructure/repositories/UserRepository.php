<?php
namespace toubilib\infrastructure\repositories;

use toubilib\core\domain\entities\praticien\User;
use toubilib\core\domain\entities\praticien\repositories\UserRepositoryInterface;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private PDO $pdoAuth)
    {
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdoAuth->prepare(
            "SELECT id, email, password, role FROM users WHERE email = :email LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $role = match ((int) $row['role']) {
            1 => 'patient',
            10 => 'praticien',
            default => 'user'
        };

        return new User(
            id: $row['id'],
            email: $row['email'],
            passwordHash: $row['password'],
            role: $role
        );
    }
}
