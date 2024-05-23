<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function getUserById(int $userId): ?User;

    public function getAllUsersWithStatus(int $status): ?Collection;
}
