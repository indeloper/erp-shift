<?php

declare(strict_types=1);

namespace App\Repositories\ExceptionNotificationUser;

use Illuminate\Database\Eloquent\Collection;

interface ExceptionNotificationUserRepositoryInterface
{

    public function flush(int $userId): void;

    public function store(int $userId, int $notificationItemId, string $channel);

    public function getUserExceptions(int $userId): Collection;

}