<?php

declare(strict_types=1);

namespace App\Repositories\NotificationItem;

use App\Models\NotificationItem;

final class NotificationItemRepository implements NotificationItemRepositoryInterface
{
    public function store(
        string $type,
        string $class,
        string $description,
        bool $status = false
    ): NotificationItem {
        return NotificationItem::query()->updateOrCreate([
            'class' => $class
        ], [
            'type' => $type,
            'description' => $description,
            'status' => $status
        ]);
    }

}