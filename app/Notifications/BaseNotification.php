<?php

namespace App\Notifications;

use App\Domain\DTO\Notification\NotificationData;
use App\Domain\Enum\NotificationChannelType;
use App\NotificationChannels\DatabaseChannel;
use App\NotificationChannels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BaseNotification extends Notification
{

    use Queueable;

    const DESCRIPTION = 'TEST NOTIFY';

    protected $notificationData;

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
     *
     * @return array
     */
    public function via($notifiable)
    {
        $channels = [];

        if ( ! $this->notificationData->getWithoutChannels()
            ->contains(NotificationChannelType::MAIL)
        ) {
            $channels[] = NotificationChannelType::MAIL;
        }

        if ( ! $this->notificationData->getWithoutChannels()
            ->contains(NotificationChannelType::SYSTEM)
        ) {
            $channels[] = DatabaseChannel::class;
        }

        if ( ! $this->notificationData->getWithoutChannels()
            ->contains(NotificationChannelType::TELEGRAM)
        ) {
            $channels[] = TelegramChannel::class;
        }

        return $channels;
    }

}
