<?php

namespace App\Listeners;

use App\Actions\Fuel\FuelActions;
use App\Events\TelegramNotificationEvent;
use App\Notifications\DefaultNotification;
use App\Notifications\Fuel\NewFuelTankResponsibleNotification;

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
    public function handle(TelegramNotificationEvent $event): void
    {
        $notificationData = $event->telegramNotificationData->getNotificationData();
        $notificationType = $notificationData->getClass();

        $message = $event->message;

        if (in_array($notificationType, [
            NewFuelTankResponsibleNotification::class,
            DefaultNotification::class,
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
