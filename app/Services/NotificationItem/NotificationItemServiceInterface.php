<?php

declare(strict_types=1);

namespace App\Services\NotificationItem;

use App\Domain\DTO\Notification\NotificationSettingsData;
use App\Models\Notification\NotificationItem;
use Illuminate\Support\Collection;

interface NotificationItemServiceInterface
{
    public function store(
        string $class,
        string $description,
        bool $status = false
    ): NotificationItem;

    public function getNotificationByClass(string $class): ?NotificationItem;

    public function getNotificationItems(int $userId): Collection;

    public function settings(
        int $userId,
        NotificationSettingsData $data
    );
}
