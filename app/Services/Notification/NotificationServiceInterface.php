<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Domain\DTO\NotificationData;
use App\Domain\DTO\NotificationSortData;
use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationServiceInterface
{

    /**
     * @param \App\Domain\DTO\NotificationData $data
     *
     * @return \App\Models\Notification
     */
    public function store(NotificationData $data): Notification;

    public function sendNotify(NotificationData $notificationData);

    public function getNotifications(int $userId, NotificationSortData $sort, int $perPage = 20): LengthAwarePaginator;

    public function delete(int $idNotify): void;

    public function view(int $idNotify): void;

}