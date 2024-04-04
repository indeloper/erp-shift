<?php

declare(strict_types=1);

namespace App\Services\Telegram;

use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

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
    )
    {
        $this->telegram = $api;
        $this->userRepository = $userRepository;
    }

    public function sendMessageUser(int $userId, string $message)
    {
        try {
            $user = $this->getUser(
                $userId
            );

            $this->telegram->sendMessage([
                'chat_id' => $user->chat_id,
                'text' => $message,
            ]);
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());
        }
    }

    public function sendRenderMessageUser(
        int $userId,
        string $pathView,
        array $data
    ) {
        try {
            $user = $this->getUser(
                $userId
            );

            $this->telegram->sendMessage([
                     'chat_id' => $user->chat_id,
                     'parse_mode' => 'HTML',
                     'text' => view($pathView, $data)->render(),
                 ]);
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());
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

}