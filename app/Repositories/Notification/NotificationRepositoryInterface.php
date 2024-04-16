<?php

declare(strict_types=1);

namespace App\Repositories\Notification;

use App\Domain\DTO\NotificationData;
use App\Models\Notification;

interface NotificationRepositoryInterface
{

    public function create(NotificationData $data): Notification;

}