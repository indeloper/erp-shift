<?php

namespace App\Notifications\Fuel;

use App\Domain\DTO\RenderTelegramNotificationData;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;

class FuelTankMovingConfirmationForOfficeResponsiblesNotification extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'Уведомление о подтверждении перемещения топливной емкости для ответственных в офисе';

    public function toTelegram($notifiable)
    {
        return new RenderTelegramNotificationData(
            $this->notificationData,
            'notifications.telegram.fuel.confirm_fuel_tank_moving_previous_responsible'
        );
    }
}
