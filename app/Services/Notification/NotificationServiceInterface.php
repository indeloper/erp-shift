<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Domain\DTO\NotificationData;
use App\Models\Notification;

interface NotificationServiceInterface
{

    /**
     * @param \App\Domain\DTO\NotificationData $data
     *
     * @return \App\Models\Notification
     */
    public function store(NotificationData $data): Notification;

    public function sendNotify(NotificationData $notificationData);

}