<?php

namespace toubilib\core\domain\entities\praticien\repositories;

use toubilib\core\domain\entities\praticien\User;


interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
}