<?php

declare(strict_types=1);

namespace App\NotificationChannels;

use App\Domain\DTO\NotificationData;
use App\Domain\DTO\RenderTelegramNotificationData;
use App\Domain\DTO\TelegramNotificationData;
use App\Services\Telegram\TelegramServiceInterface;
use Exception;
use Telegram\Bot\Laravel\Facades\Telegram;

final class TelegramChannel
{
    private $telegramService;

    public function __construct(
        TelegramServiceInterface $telegramService
    )
    {
        $this->telegramService = $telegramService;
    }
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, $notification)
    {
        /** @var \App\Domain\DTO\TelegramNotificationData|\App\Domain\DTO\RenderTelegramNotificationData $data */
        $data = $notification->toTelegram($notifiable);

        if ($data instanceof RenderTelegramNotificationData) {
            return $this->telegramService->sendRenderMessageUser(
                $data->getNotificationData()->getUserId(),
                $data->getPathView(),
                $data->getNotificationData()->getData()
            );
        }

        if ($data instanceof TelegramNotificationData) {
            return $this->telegramService->sendMessageUser(
                $data->getNotificationData()->getUserId(),
                $data->getNotificationData()->getName()
            );
        }



        throw new Exception('Нужно передать TelegramNotificationData или RenderTelegramNotificationData');



    }
}