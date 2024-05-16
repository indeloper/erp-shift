<?php

declare(strict_types=1);

namespace App\Repositories\Notification;

use App\Domain\DTO\Notification\NotificationData;
use App\Domain\DTO\Notification\NotificationSortData;
use App\Models\Notification\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface
{
    public function create(NotificationData $data): Notification;

    public function getNotifications(int $userId, NotificationSortData $sort, int $perPage = 20): LengthAwarePaginator;

    public function delete(int $idNotify): void;

    public function view(int $idNotify): void;

    public function viewAll(int $id): void;
}
