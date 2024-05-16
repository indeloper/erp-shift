<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User;

final class UserRepository implements UserRepositoryInterface
{

    /**
     * @param int $userId
     *
     * @return User|null
     */
    public function getUserById(int $userId): ?User
    {
        return User::query()
            ->find($userId);
    }

}