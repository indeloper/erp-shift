<?php

namespace App\Notifications\Labor;

use App\Domain\DTO\TelegramNotificationData;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class LaborSignedNotification extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'TEST NOTIFY';

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->markdown('mail.default-notification', [
                'name' => $this->notificationData->getName(),
                'description' => $this->notificationData->getDescription(),
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
