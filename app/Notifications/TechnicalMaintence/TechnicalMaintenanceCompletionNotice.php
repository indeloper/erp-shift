<?php

namespace App\Notifications\TechnicalMaintence;

use App\Domain\DTO\RenderTelegramNotificationData;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class TechnicalMaintenanceCompletionNotice extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'Уведомление о завершении проведения технических работ';

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(self::DESCRIPTION)
            ->markdown('notifications.mail.technical_maintenance.technical-maintenance-notification', [
                'name' => $this->notificationData->getName(),
                'info' => $this->notificationData->getAdditionalInfo(),
                'url'  => $this->notificationData->getUrl(),
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
