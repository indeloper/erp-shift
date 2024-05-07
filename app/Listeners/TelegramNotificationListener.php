<?php

namespace App\Listeners;

use App\Actions\Fuel\FuelActions;
use App\Domain\Enum\NotificationType;
use App\Events\TelegramNotificationEvent;

class TelegramNotificationListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(TelegramNotificationEvent $event)
    {
        $notificationData = $event->telegramNotificationData->getNotificationData();
        $notificationType = $notificationData->getClass();

        $message = $event->message;

        if (in_array($notificationType, [
            NotificationType::FUEL_NEW_TANK_RESPONSIBLE,
            NotificationType::FUEL_NOT_AWAITING_CONFIRMATION
        ])) {
            (new FuelActions)->storeFuelTankChatMessageTmp(
                $notificationData->getData()['tank_id'],
                $message->chat->id,
                $notificationData->getName(),
                $message->messageId
            );
        }
    }
}
