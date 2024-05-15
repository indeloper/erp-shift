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
     * @param  int|int[]  $users
     * @return void
     */
    public static function send($users, array $notificationData)
    {
        if (! is_array($users)) {
            $users = [$users];
        }

        foreach ($users as $user) {
            dispatchNotify(
                $user,
                static::class,
                $notificationData
            );
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        $channels = [];

        $notificationClass = static::class;

        if ($this->checkChannel($notificationClass,
            NotificationChannelType::MAIL, 'toMail')
        ) {
            $channels[] = NotificationChannelType::MAIL;
        }

        if ($this->checkChannel($notificationClass,
            NotificationChannelType::SYSTEM, 'toDatabase')
        ) {
            $channels[] = DatabaseChannel::class;
        }

        if ($this->checkChannel($notificationClass,
            NotificationChannelType::TELEGRAM, 'toTelegram')
        ) {
            $channels[] = TelegramChannel::class;
        }

        return $channels;
    }

    private function checkChannel(
        string $class,
        string $channel,
        string $method
    ): bool {
        return ! $this->notificationData->getWithoutChannels()
            ->contains($channel)
            && method_exists($class, $method);
    }
}
