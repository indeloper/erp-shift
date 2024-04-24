<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Domain\DTO\Notification\NotificationData;
use App\Domain\DTO\Notification\NotificationSortData;
use App\Domain\Enum\NotificationType;
use App\Models\Notification\Notification;
use App\Repositories\Notification\NotificationRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\NotificationItem\NotificationItemServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

final class NotificationService implements NotificationServiceInterface
{
    private $notificationRepository;
    private $userRepository;
    private $notificationItemService;
    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        UserRepositoryInterface $userRepository,
        NotificationItemServiceInterface $notificationItemService
    )
    {
        $this->notificationRepository = $notificationRepository;
        $this->userRepository = $userRepository;
        $this->notificationItemService = $notificationItemService;
    }

    /**
     * @param \App\Domain\DTO\Notification\NotificationData $data
     *
     * @return \App\Models\Notification\Notification
     */
    public function store(NotificationData $data): Notification
    {
        return $this->notificationRepository->create(
            $data
        );
    }

    /**
     * @param  \App\Domain\DTO\Notification\NotificationData  $notificationData
     *
     * @return void
     */
    public function sendNotify(NotificationData $notificationData): void
    {
        $user = $this->userRepository->getUserById(
            $notificationData->getUserId()
        );

        $notification = $this->notificationItemService->getNotificationByType(
            $notificationData->getType()
        );

        if ($notification === null) {
            Log::error('Нет такого уведомления');
            return;
        }

        if ($user === null) {
            Log::error('Нет пользователя для отправки уведомления');
            return;
        }

//        if (!Gate::forUser($user)->any($notification->permissions->pluck('codename'))) {
//            Log::error('Нет прав');
//            return;
//        }

        $notificationClass = NotificationType::determinateNotificationClassByType(
            $notificationData->getType()
        );

        $notificationData->setWithoutChannels(
            $notification->exceptions
                ->filter(function ($exception) use ($user) {
                    return $exception->user_id !== $user->id;
                })
                ->pluck('pivot')
                ->pluck('channel')
        );

        $user->notify(
            new $notificationClass(
                $notificationData
            )
        );
    }

    public function getNotifications(
        int $userId,
        NotificationSortData $sort,
        int $perPage = 20
    ): LengthAwarePaginator {
        return $this->notificationRepository->getNotifications(
            $userId,
            $sort,
            $perPage
        );
    }

    public function delete(int $idNotify): void
    {
        $this->notificationRepository->delete(
            $idNotify
        );
    }

    public function view(int $idNotify): void
    {
        $this->notificationRepository->view($idNotify);
    }

    public function viewAll(int $id): void
    {
       $this->notificationRepository->viewAll($id);
    }

}