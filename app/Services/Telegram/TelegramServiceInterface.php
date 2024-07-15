<?php

declare(strict_types=1);

namespace App\Services\Telegram;

use App\Domain\DTO\TelegramNotificationData;
use Telegram\Bot\Objects\Message;

interface TelegramServiceInterface
{
    public function sendMessageUser(
        TelegramNotificationData $data
    ): ?Message;

    public function sendRenderMessageUser(
        TelegramNotificationData $data,
        string $pathView
    ): ?Message;

    public function editMessageText(
        string $chatId,
        string $messageId,
        string $text
    );
}
