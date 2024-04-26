<?php

namespace App\Notifications\Object;

use App\Domain\DTO\RenderTelegramNotificationData;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class ResponsibleAddedToObjectNotice extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'Добавлен ответственный на объект';

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
