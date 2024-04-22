<?php

namespace App\Notifications\Fuel;

use App\Domain\DTO\NotificationData;
use App\Domain\DTO\RenderTelegramNotificationData;
use App\NotificationChannels\DatabaseChannel;
use App\NotificationChannels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FuelTanksLevelCheckNotification extends Notification
{
    use Queueable;

    const DESCRIPTION = 'Уведомление о проверке уровня топлива в емкостях.';

    private $notificationData;

    public function __construct(NotificationData $notificationData)
    {
        $this->notificationData = $notificationData;
    }

    public function via($notifiable)
    {
        return [
            'mail',
            DatabaseChannel::class,
            TelegramChannel::class,
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->notificationData->getDescription())
            ->markdown('mail.fuel.new-fuel-tank-responsible-notification', [
                'name' => $this->notificationData->getName(),
                'link' => $this->notificationData->getAdditionalInfo(),
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
            'telegram.fuel.fuel_tanks_level_check'
        );
    }
}
