<?php

namespace App\Notifications\Fuel;

use App\Domain\DTO\RenderTelegramNotificationData;
use App\Domain\Enum\TelegramEventType;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Telegram\Bot\Keyboard\Keyboard;

class NewFuelTankResponsibleNotification extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'Уведомление о новом ответственном за топливную емкость';

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(self::DESCRIPTION)
            ->markdown('notifications.mail.fuel.new-fuel-tank-responsible-notification', [
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
        return (new RenderTelegramNotificationData(
            $this->notificationData,
            'notifications.telegram.fuel.fuel_tank_movement_confirmation'
        ))
            ->setKeyboard(
                Keyboard::make()
                    ->inline()
                    ->row(
                        Keyboard::inlineButton([
                            'text' => 'Подтвердить',
                            'callback_data' => json_encode([
                                'eventName' => TelegramEventType::FUEL_TANK_MOVEMENT_CONFIRMATION,
                                'eventId' => $this->notificationData->getData()['tank_id'],
                            ]),
                        ])
                    )
            );
    }
}
