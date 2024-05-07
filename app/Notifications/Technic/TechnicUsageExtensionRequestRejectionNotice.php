<?php

namespace App\Notifications\Technic;

use App\Domain\DTO\RenderTelegramNotificationData;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class TechnicUsageExtensionRequestRejectionNotice extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'Уведомление об отклонении запроса на продление использования техники';

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(self::DESCRIPTION)
            ->markdown('notifications.mail.technic.technic-notification', [
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
