<?php

namespace App\Notifications\Material;

use App\Domain\DTO\RenderTelegramNotificationData;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class MaterialDifferenceNotice extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'Уведомление о расхождении отправленного и полученного материала';

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->notificationData->getDescription())
            ->markdown('mail.material.material-notification', [
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
