<?php

declare(strict_types=1);

namespace App\Domain\DTO;

use App\Domain\DTO\Notification\NotificationData;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramNotificationData
{
    protected $notificationData;

    protected $keyboard;

    public function __construct(
        NotificationData $notificationData
    ) {
        $this->notificationData = $notificationData;
    }

    public function getNotificationData(): NotificationData
    {
        return $this->notificationData;
    }

    public function getKeyboard(): ?Keyboard
    {
        return $this->keyboard;
    }

    public function setKeyboard(Keyboard $keyboard): self
    {
        $this->keyboard = $keyboard;

        return $this;
    }
}
