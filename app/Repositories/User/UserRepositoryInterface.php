<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User;

interface UserRepositoryInterface
{

    /**
     * @param int $userId
     *
     * @return \App\Models\User
     */
    public function getUserById(int $userId): ?User;
}