<?php

declare(strict_types=1);

namespace App\Services\User;

interface UserServiceInterface
{
    public function getUserById($userId);
}
