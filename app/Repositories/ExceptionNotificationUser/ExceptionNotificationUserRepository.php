<?php

declare(strict_types=1);

namespace App\Repositories\ExceptionNotificationUser;

use App\Models\Notification\ExceptionNotificationUser;
use Illuminate\Database\Eloquent\Collection;

final class ExceptionNotificationUserRepository
    implements ExceptionNotificationUserRepositoryInterface
{

    public function flush(int $userId): void
    {
        ExceptionNotificationUser::where('user_id', $userId)->delete();
    }

    public function store(int $userId, int $notificationItemId, string $channel)
    {
        ExceptionNotificationUser::query()->create([
            'user_id'              => $userId,
            'notification_item_id' => $notificationItemId,
            'channel'              => $channel,
        ]);
    }

    public function getUserExceptions(int $userId): Collection
    {
        return ExceptionNotificationUser::query()
            ->where('user_id', $userId)
            ->get();
    }

}