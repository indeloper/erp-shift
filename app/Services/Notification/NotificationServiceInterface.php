<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Domain\DTO\Notification\NotificationData;
use App\Domain\DTO\Notification\NotificationSortData;
use App\Models\Notification\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationServiceInterface
{

    /**
     * @param \App\Domain\DTO\Notification\NotificationData $data
     *
     * @return \App\Models\Notification\Notification
     */
    public function store(NotificationData $data): Notification;

    public function sendNotify(NotificationData $notificationData);

    public function getNotifications(int $userId, NotificationSortData $sort, int $perPage = 20): LengthAwarePaginator;

    public function delete(int $idNotify): void;

    public function view(int $idNotify): void;

    public function viewAll(int $id): void;

}