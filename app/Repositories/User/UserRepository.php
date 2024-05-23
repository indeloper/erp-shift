<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class UserRepository implements UserRepositoryInterface
{
    public function getUserById(int $userId): ?User
    {
        return User::query()
            ->find($userId);
    }

    public function getAllUsersWithStatus(int $status): ?Collection
    {
        return User::getAllUsers()->where('status', $status)->get();
    }
}
