<?php

declare(strict_types=1);

namespace App\Repositories\NotificationItem;

use App\Models\NotificationItem;
use Illuminate\Database\Eloquent\Collection;

final class NotificationItemRepository
    implements NotificationItemRepositoryInterface
{

    public function store(
        string $type,
        string $class,
        string $description,
        bool $status = false
    ): NotificationItem {
        return NotificationItem::query()->updateOrCreate([
            'type' => $type,

        ], [
            'class'       => $class,
            'description' => $description,
            'status'      => $status,
        ]);
    }

    public function getNotificationByType(int $type): ?NotificationItem
    {
        return NotificationItem::query()
            ->where('type', $type)
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
