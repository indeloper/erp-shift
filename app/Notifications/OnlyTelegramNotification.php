<?php

namespace App\Notifications;

use App\Domain\DTO\RenderTelegramNotificationData;
use Illuminate\Bus\Queueable;

class OnlyTelegramNotification extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'ONLY TELEGRAM NOTIFY';

    public function toTelegram()
    {
        return new RenderTelegramNotificationData(
            $this->notificationData,
            'notifications.telegram.example'
        );
    }
}
