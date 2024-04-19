<?php

declare(strict_types=1);

namespace App\Repositories\NotificationItem;

use App\Models\NotificationItem;
use Illuminate\Database\Eloquent\Collection;

interface NotificationItemRepositoryInterface
{
    public function store(
        string $type,
        string $class,
        string $description,
        bool $status = false
    ): NotificationItem;

    public function getNotificationByType(int $type): ?NotificationItem;

    public function getNotifications(): Collection;

}