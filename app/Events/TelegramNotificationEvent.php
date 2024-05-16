<?php

namespace App\Events;

use App\Domain\DTO\TelegramNotificationData;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Telegram\Bot\Objects\Message;

class TelegramNotificationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public $telegramNotificationData;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        Message $message,
        TelegramNotificationData $telegramNotificationData
    ) {
        $this->message = $message;
        $this->telegramNotificationData = $telegramNotificationData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name')
        ];
    }
}
