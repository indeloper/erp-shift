<?php

declare(strict_types=1);

namespace App\Services\NotificationItem;

use App\Models\NotificationItem;

interface NotificationItemServiceInterface
{
    public function store(
        string $type,
        string $class,
        string $description,
        bool $status = false
    ): NotificationItem;
}