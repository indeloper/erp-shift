<?php

namespace App\Notifications\Exceptions;

use App\Domain\DTO\RenderTelegramNotificationData;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class ExceptionNotice extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'Новая ошибка в ERP-системе';

    /*public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(self::DESCRIPTION)
            ->markdown('notifications.mail.exception-notice-notification', [
                'name' => $this->notificationData->getName(),
                'info' => $this->notificationData->getAdditionalInfo(),
                'url'  => $this->notificationData->getUrl(),
            ]);
    }*/

    /*public function toDatabase($notifiable)
    {
        return $this->notificationData;
    }*/

    public function toTelegram($notifiable)
    {
        return new RenderTelegramNotificationData(
            $this->notificationData,
            'notifications.telegram.exception-notice-notification'
        );
    }
}
