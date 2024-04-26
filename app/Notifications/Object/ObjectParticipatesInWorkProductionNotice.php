<?php

namespace App\Notifications\Object;

use App\Domain\DTO\RenderTelegramNotificationData;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class ObjectParticipatesInWorkProductionNotice extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'Объект участвует в производстве работ';

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->notificationData->getDescription())
            ->markdown('mail.object.object-notification', [
                'name' => $this->notificationData->getName(),
                'info' => $this->notificationData->getAdditionalInfo(),
                'url'  => $this->notificationData->getUrl(),
                'description' => $this->notificationData->getDescription(),
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
            'telegram.default-with-url'
        );
    }
}
