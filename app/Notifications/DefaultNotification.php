<?php

namespace App\Notifications;

use App\Domain\DTO\TelegramNotificationData;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class DefaultNotification extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'TEST NOTIFY';

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
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

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
