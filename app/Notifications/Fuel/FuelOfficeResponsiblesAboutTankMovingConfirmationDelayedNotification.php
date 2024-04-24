<?php

namespace App\Notifications\Fuel;

use App\Domain\DTO\RenderTelegramNotificationData;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class FuelOfficeResponsiblesAboutTankMovingConfirmationDelayedNotification extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'TEST NOTIFY';


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
        return new RenderTelegramNotificationData(
            $this->notificationData,
            'telegram.fuel.fuel_notify_office_responsibles_about_tank_moving_confirmation_delayed'
        );
    }


}
