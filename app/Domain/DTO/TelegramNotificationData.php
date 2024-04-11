<?php

declare(strict_types=1);

namespace App\Domain\DTO;

use App\Domain\Enum\NotificationType;
use App\Notifications\DefaultNotification;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramNotificationData
{
    protected $notificationData;
    protected $keyboard;


    public function __construct(
        NotificationData $notificationData
    )
    {
        $this->notificationData = $notificationData;
    }

    /**
     * @return \App\Domain\DTO\NotificationData
     */
    public function getNotificationData(): NotificationData
    {
        return $this->notificationData;
    }

    /**
     * @return \Telegram\Bot\Keyboard\Keyboard
     */
    public function getKeyboard(): ?Keyboard
    {
        return $this->keyboard;
    }

    /**
     * @param  \Telegram\Bot\Keyboard\Keyboard  $keyboard
     *
     * @return $this
     */
    public function setKeyboard(Keyboard $keyboard): self
    {
        $this->keyboard = $keyboard;

        return $this;
    }

}