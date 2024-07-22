<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User;

final class UserRepository implements UserRepositoryInterface
{

    public function getUserById(int $userId): ?User
    {
        return User::query()
            ->find($userId);
    }

    public function getUserByTelegramId(int $telegramId): ?User
    {
        return User::query()
            ->withoutGlobalScopes()
            ->where('chat_id', $telegramId)
            ->first();
    }

}
