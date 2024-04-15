<?php

declare(strict_types=1);

namespace App\Services\NotificationItem;

use App\Models\NotificationItem;
use App\Repositories\NotificationItem\NotificationItemRepositoryInterface;

final class NotificationItemService implements NotificationItemServiceInterface
{
    private $notificationItemRepository;

    public function __construct(
        NotificationItemRepositoryInterface $notificationItemRepository
    ) {
        $this->notificationItemRepository = $notificationItemRepository;
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

}