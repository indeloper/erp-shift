<?php

declare(strict_types=1);

namespace App\Domain\DTO;

use App\Domain\Enum\NotificationType;
use App\Notifications\DefaultNotification;

 class TelegramNotificationData
{
    protected $notificationData;


    public function __construct(
        NotificationData $notificationData
    )
    {
        $this->notificationData = $notificationData;
    }

    /**
     * @return \App\Domain\DTO\NotificationData
     */
    public function getNotificationData(): NotificationData
    {
        return $this->notificationData;
    }

}