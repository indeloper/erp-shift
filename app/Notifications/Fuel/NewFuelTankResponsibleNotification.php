<?php

namespace App\Notifications\Fuel;

use App\Domain\DTO\Notification\NotificationData;
use App\Domain\DTO\RenderTelegramNotificationData;
use App\Domain\Enum\TelegramEventType;
use App\NotificationChannels\DatabaseChannel;
use App\NotificationChannels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Telegram\Bot\Keyboard\Keyboard;

class NewFuelTankResponsibleNotification extends Notification
{
    use Queueable;

    const DESCRIPTION = 'TEST NOTIFY';

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
                'description' => $this->notificationData->getDescription(),
            ]);
    }

    public function toDatabase($notifiable)
    {
        return $this->notificationData;
    }

    public function toTelegram($notifiable)
    {
        return (new RenderTelegramNotificationData(
            $this->notificationData,
            'telegram.fuel.fuel_tank_movement_confirmation'
        ))
            ->setKeyboard(
                Keyboard::make()
                    ->inline()
                    ->row(
                        Keyboard::inlineButton([
                            'text' => 'Подтвердить',
                            'callback_data' => json_encode([
                                'eventName' => TelegramEventType::FUEL_TANK_MOVEMENT_CONFIRMATION,
                                'eventId' => $this->notificationData->getData()['tank_id']
                            ])
                        ])
                    )
        );
    }
}
