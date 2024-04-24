<?php

declare(strict_types=1);

namespace App\NotificationChannels;

use App\Domain\DTO\RenderTelegramNotificationData;
use App\Domain\DTO\TelegramNotificationData;
use App\Events\TelegramNotificationEvent;
use App\Services\Telegram\TelegramServiceInterface;
use Exception;
use Telegram\Bot\Objects\Message;

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

            $response = $this->telegramService->sendRenderMessageUser(
                $data,
                $data->getPathView()
            );

            $this->dispatchEvent($response, $data);

        } elseif ($data instanceof TelegramNotificationData) {

            $response = $this->telegramService->sendMessageUser($data);

            $this->dispatchEvent($response, $data);

        } else {
            throw new Exception('Нужно передать TelegramNotificationData или RenderTelegramNotificationData');
        }

    }

    private function dispatchEvent(?Message $response, TelegramNotificationData $data)
    {
        if ($response) {
            event(
                new TelegramNotificationEvent($response, $data)
            );
        }

    }
}