<?php

declare(strict_types=1);

namespace App\Services\NotificationItem;

use App\Domain\DTO\Notification\NotificationSettingsData;
use App\Domain\Enum\NotificationChannelType;
use App\Models\Notification\NotificationItem;
use App\Repositories\ExceptionNotificationUser\ExceptionNotificationUserRepositoryInterface;
use App\Repositories\NotificationItem\NotificationItemRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

final class NotificationItemService implements NotificationItemServiceInterface
{

    private $notificationItemRepository;

    private $userRepository;

    private $exceptionNotificationUserRepository;

    public function __construct(
        NotificationItemRepositoryInterface $notificationItemRepository,
        UserRepositoryInterface $userRepository,
        ExceptionNotificationUserRepositoryInterface $exceptionNotificationUserRepository
    ) {
        $this->notificationItemRepository = $notificationItemRepository;
        $this->userRepository             = $userRepository;

        $this->exceptionNotificationUserRepository
            = $exceptionNotificationUserRepository;
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

    public function getNotificationByClass(string $class): ?NotificationItem
    {
        return $this->notificationItemRepository
            ->getNotificationByClass($class);
    }

    public function getNotificationItems(int $userId): Collection
    {
        $notifications = $this->notificationItemRepository
            ->getNotifications();

        $exceptions
            = $this->exceptionNotificationUserRepository->getUserExceptions(
            $userId
        );

        $notifications = $this->determinateException($notifications,
            $exceptions);

//        $notifications = $this->determinateGates($notifications, $userId);

        return $notifications;
    }

    public function settings(int $userId, NotificationSettingsData $data)
    {
        $this->exceptionNotificationUserRepository->flush(
            $userId
        );

        foreach ($data->getItems() as $item) {
            if ( ! $item->isTelegram()) {
                $this->exceptionNotificationUserRepository->store(
                    $userId,
                    $item->getId(),
                    NotificationChannelType::TELEGRAM
                );
            }

            if ( ! $item->isMail()) {
                $this->exceptionNotificationUserRepository->store(
                    $userId,
                    $item->getId(),
                    NotificationChannelType::MAIL
                );
            }

            if ( ! $item->isSystem()) {
                $this->exceptionNotificationUserRepository->store(
                    $userId,
                    $item->getId(),
                    NotificationChannelType::SYSTEM
                );
            }
        }
    }

    private function determinateException(
        Collection $notifications,
        Collection $exceptions
    ): Collection {
        return $notifications->map(function (NotificationItem $notificationItem
        ) use ($exceptions) {
            $notificationExceptions = $exceptions->where('notification_item_id',
                $notificationItem->id);

            foreach (NotificationChannelType::values() as $channel) {
                $notificationItem->{$channel} = true;
            }

            if ($notificationExceptions->isNotEmpty()) {
                foreach ($notificationExceptions as $exception) {
                    $notificationItem->{$exception->channel} = false;
                }
            }

            return $notificationItem;
        });
    }

    private function determinateGates(
        Collection $notifications,
        int $userId
    ): Collection {
        $user = $this->userRepository->getUserById(
            $userId
        );

        return $notifications->filter(function (
            NotificationItem $notificationItem
        ) use ($user) {
            return Gate::forUser($user)
                ->any($notificationItem->permissions->pluck('codename'));
        });
    }

}
