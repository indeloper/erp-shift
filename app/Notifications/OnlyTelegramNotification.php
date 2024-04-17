<?php

namespace App\Notifications;

use App\Domain\DTO\NotificationData;
use App\Domain\DTO\RenderTelegramNotificationData;
use App\NotificationChannels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OnlyTelegramNotification extends Notification
{
    use Queueable;

    const DESCRIPTION = 'ONLY TELEGRAM NOTIFY';

    private $notificationData;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(NotificationData $notificationData)
    {
        $this->notificationData = $notificationData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
            TelegramChannel::class,
        ];
    }

    public function toTelegram()
    {
        return new RenderTelegramNotificationData(
            $this->notificationData,
            'telegram.example'
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
