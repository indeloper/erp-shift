<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User;

final class UserRepository implements UserRepositoryInterface
{

    /**
     * @param $userId
     *
     * @return User|null
     */
    public function getUserById($userId)
    {
        return User::query()
            ->find($userId);
    }

}