<?php

declare(strict_types=1);

namespace App\Services\NotificationItem;

use App\Models\NotificationItem;
use App\Repositories\NotificationItem\NotificationItemRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Collection;

final class NotificationItemService implements NotificationItemServiceInterface
{

    private $notificationItemRepository;

    private $userRepository;

    public function __construct(
        NotificationItemRepositoryInterface $notificationItemRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->notificationItemRepository = $notificationItemRepository;
        $this->userRepository             = $userRepository;
    }

    public function store(
        string $type,
        string $class,
        string $description,
        bool $status = false
    ): NotificationItem {
        return $this->notificationItemRepository->store(
            $type,
            $class,
            $description,
            $status
        );
    }

    public function getNotificationByType(int $type): ?NotificationItem
    {
        return $this->notificationItemRepository
            ->getNotificationByType($type);
    }

    public function getNotificationItems(int $userId): Collection
    {
        $notifications = $this->notificationItemRepository
            ->getNotifications();

//        $user = $this->userRepository->getUserById(
//            $userId
//        );

//        return $notifications->filter(function (
//            NotificationItem $notificationItem
//        ) use ($user) {
//            return Gate::forUser($user)
//                ->any($notificationItem->permissions->pluck('codename'));
//        });

        return $notifications->filter(function () {
            return true;
        });
    }

}