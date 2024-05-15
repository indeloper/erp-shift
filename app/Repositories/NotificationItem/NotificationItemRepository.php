<?php

declare(strict_types=1);

namespace App\Repositories\NotificationItem;

use App\Models\Notification\NotificationItem;
use Illuminate\Database\Eloquent\Collection;

final class NotificationItemRepository
    implements NotificationItemRepositoryInterface
{

    public function store(
        string $class,
        string $description,
        bool $status = false
    ): NotificationItem {
        return NotificationItem::query()->updateOrCreate([
            'class'       => $class,
        ], [
            'description' => $description,
            'status'      => $status,
        ]);
    }

    public function getNotificationByClass(string $class): ?NotificationItem
    {
        return NotificationItem::query()
            ->where('class', $class)
            ->where('status', true)
            ->first();
    }

    public function getNotifications(): Collection
    {
        return NotificationItem::query()
            ->with(['permissions'])
            ->where('status', true)
            ->get();
    }

}
