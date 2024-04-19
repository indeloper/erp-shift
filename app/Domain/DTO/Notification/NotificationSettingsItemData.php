<?php

declare(strict_types=1);

namespace App\Domain\DTO\Notification;

final class NotificationSettingsItemData
{

    private $id;

    private $mail;

    private $telegram;

    private $system;

    public function __construct(
        int $id,
        bool $mail,
        bool $telegram,
        bool $system
    ) {
        $this->id       = $id;
        $this->mail     = $mail;
        $this->telegram = $telegram;
        $this->system   = $system;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isMail(): bool
    {
        return $this->mail;
    }

    public function isTelegram(): bool
    {
        return $this->telegram;
    }

    public function isSystem(): bool
    {
        return $this->system;
    }

}