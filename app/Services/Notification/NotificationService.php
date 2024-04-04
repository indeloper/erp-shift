<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Domain\DTO\NotificationData;
use App\Domain\Enum\NotificationType;
use App\Helpers\NotificationSupport;
use App\Models\Notification;
use App\Repositories\Notification\NotificationRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;

final class NotificationService implements NotificationServiceInterface
{
    private $notificationRepository;
    private $userRepository;
    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->notificationRepository = $notificationRepository;
        $this->userRepository = $userRepository;
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

        if ($user === null) {
            Log::error('Нет пользователя для отправки уведомления');
        }

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