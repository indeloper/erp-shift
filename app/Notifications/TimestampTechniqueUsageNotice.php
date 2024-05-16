<?php

namespace App\Notifications;

use App\Domain\DTO\RenderTelegramNotificationData;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class TimestampTechniqueUsageNotice extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'Уведомление о задаче "Отметка времени использования техники"';

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(self::DESCRIPTION)
            ->markdown('notifications.mail.timestamp-of-technique-usage-notification', [
                'name' => $this->notificationData->getName(),
                'info' => $this->notificationData->getAdditionalInfo(),
                'url' => $this->notificationData->getUrl(),
            ]);
    }

    public function toDatabase($notifiable)
    {
        return $this->notificationData;
    }

    public function toTelegram($notifiable)
    {
        return new RenderTelegramNotificationData(
            $this->notificationData,
            'notifications.telegram.default-with-url'
        );
    }
}
