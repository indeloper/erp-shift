<?php

declare(strict_types=1);

namespace App\Repositories\NotificationItem;

use App\Models\Notification\NotificationItem;
use Illuminate\Database\Eloquent\Collection;

interface NotificationItemRepositoryInterface
{
    public function store(
        string $class,
        string $description,
        bool $status = false
    ): NotificationItem;

    public function getNotificationByClass(string $class): ?NotificationItem;

    public function getNotifications(): Collection;
}
