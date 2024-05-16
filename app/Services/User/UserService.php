<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Repositories\User\UserRepositoryInterface;

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
}
