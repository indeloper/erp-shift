<?php

declare(strict_types=1);

namespace App\Services\Telegram;

interface TelegramServiceInterface
{
    public function sendMessageUser(int $userId, string $message);

    public function sendRenderMessageUser(
        int $userId,
        string $getPathView,
        array $data
    );

}