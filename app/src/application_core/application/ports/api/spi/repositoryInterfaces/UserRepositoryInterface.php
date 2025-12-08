<?php

namespace toubilib\core\application\ports\api\spi\repositoryInterfaces;

use toubilib\core\domain\entities\User;


interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
}