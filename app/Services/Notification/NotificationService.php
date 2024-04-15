<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Domain\DTO\NotificationData;
use App\Domain\Enum\NotificationType;
use App\Helpers\NotificationSupport;
use App\Models\Notification;
use App\Repositories\Notification\NotificationRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\NotificationItem\NotificationItemServiceInterface;
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
     * @param \App\Domain\DTO\NotificationData $data
     *
     * @return \App\Models\Notification
     */
    public function store(NotificationData $data): Notification
    {
        return $this->notificationRepository->create(
            $data
        );
    }

    /**
     * @param  \App\Domain\DTO\NotificationData  $notificationData
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

        $user->notify(
            new $notificationClass(
                $notificationData
            )
        );
    }

}