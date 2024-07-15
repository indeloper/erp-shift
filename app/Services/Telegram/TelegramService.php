<?php

declare(strict_types=1);

namespace App\Services\Telegram;

use App\Domain\DTO\TelegramNotificationData;
use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Message;

final class TelegramService implements TelegramServiceInterface
{

    /** @var \Telegram\Bot\Api */
    private $telegram;

    /**
     * @var \App\Repositories\User\UserRepositoryInterface
     */
    private $userRepository;

    public function __construct(
        Api $api,
        UserRepositoryInterface $userRepository
    ) {
        $this->telegram       = $api;
        $this->userRepository = $userRepository;
    }

    public function sendMessageUser(TelegramNotificationData $data): ?Message
    {
        try {
            $user = $this->getUser(
                $data->getNotificationData()->getUserId()
            );

            return $this->telegram->sendMessage([
                'chat_id' => $user->chat_id,
                'text'    => $data->getNotificationData()->getName(),
            ]);
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());

            return null;
        }
    }

    public function sendRenderMessageUser(
        TelegramNotificationData $data,
        string $pathView
    ): ?Message {
        try {
            $user = $this->getUser(
                $data->getNotificationData()->getUserId()
            );

            return $this->telegram->sendMessage([
                'chat_id'      => $user->chat_id,
                'parse_mode'   => 'HTML',
                'reply_markup' => $data->getKeyboard() ??
                    json_encode(['inline_keyboard' => []]),
                'text'         => view($pathView, [
                    'notificationData' => $data->getNotificationData(),
                ])->render(),
            ]);
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());

            return null;
        }
    }

    private function getUser(int $userId): User
    {
        $user = $this->userRepository->getUserById($userId);

        if ($user === null || $user->chat_id === null) {
            throw new Exception('Пользователя нет');
        }

        return $user;
    }

    public function editMessageText(
        string $chatId,
        string $messageId,
        string $text
    ) {
        try {
            $this->telegram->editMessageText([
                'chat_id'      => $chatId,
                'message_id'   => $messageId,
                'parse_mode'   => 'HTML',
                'reply_markup' => json_encode(['inline_keyboard' => []]),
                'text'         => $text,
            ]);
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());
        }
    }

}
