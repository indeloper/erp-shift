<?php

declare(strict_types=1);

namespace App\Services\NotificationItem;

use App\Models\NotificationItem;
use Illuminate\Support\Collection;

interface NotificationItemServiceInterface
{
    public function store(
        string $type,
        string $class,
        string $description,
        bool $status = false
    ): NotificationItem;

    public function getNotificationByType(int $type): ?NotificationItem;

    public function getNotificationItems(int $userId): Collection;

}