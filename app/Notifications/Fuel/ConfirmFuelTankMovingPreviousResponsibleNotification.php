<?php

namespace App\Notifications\Fuel;

use App\Domain\DTO\NotificationData;
use App\Domain\DTO\RenderTelegramNotificationData;
use App\NotificationChannels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ConfirmFuelTankMovingPreviousResponsibleNotification extends Notification
{
    use Queueable;

    const DESCRIPTION = 'TEST NOTIFY';

    private $notificationData;

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

    public function toTelegram($notifiable)
    {
        return new RenderTelegramNotificationData(
            $this->notificationData,
            'telegram.fuel.confirm_fuel_tank_moving_previous_responsible'
        );
    }
}
