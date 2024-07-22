<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Domain\DTO\User\UpdateUserData;
use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

final class UserService implements UserServiceInterface
{

    /** @var UserRepositoryInterface */
    public $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    public function getUserById($userId)
    {
        return $this->userRepository->getUserById(
            $userId
        );
    }

    public function getUserTelegram(int $telegramId): User
    {
        $user = $this->userRepository->getUserByTelegramId($telegramId);

        if ($user === null) {
            $user = User::query()->create([
                'password' => Hash::make('123456789'),
                'chat_id'  => $telegramId,
            ]);
        }

        return $user;
    }

    public function updateUser(
        User $user,
        UpdateUserData $data
    ): User {
        $user->update([
            'email'        => $data->email,
            'INN'          => $data->INN,
            'first_name'   => $data->first_name,
            'last_name'    => $data->last_name,
            'patronymic'   => $data->patronymic,
            'birthday'     => $data->birthday,
            'person_phone' => $data->person_phone,
            'work_phone'   => $data->work_phone,
        ]);

        return $user->refresh();
    }

}
