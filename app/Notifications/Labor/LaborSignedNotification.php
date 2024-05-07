<?php

namespace App\Notifications\Labor;

use App\Domain\DTO\TelegramNotificationData;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class LaborSignedNotification extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'Документы по заявке на формирование приказов подписаны';

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->markdown('notifications.mail.default-notification', [
                'name' => $this->notificationData->getName(),
                'link' => $this->notificationData->getAdditionalInfo(),
            ]);
    }

    public function toDatabase($notifiable)
    {
        return $this->notificationData;
    }

    public function toTelegram($notifiable)
    {
        return new TelegramNotificationData(
            $this->notificationData
        );
    }
}
