<?php

declare(strict_types=1);

namespace App\Repositories\Notification;

use App\Domain\DTO\NotificationData;
use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface
{

    public function create(NotificationData $data): Notification;

    public function getNotifications(int $userId, int $perPage = 20): LengthAwarePaginator;

}