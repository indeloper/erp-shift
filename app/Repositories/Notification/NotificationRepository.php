<?php

declare(strict_types=1);

namespace App\Repositories\Notification;

use App\Domain\DTO\NotificationData;
use App\Models\Notification;

final class NotificationRepository implements NotificationRepositoryInterface
{
    public function create(NotificationData $data): Notification
    {
        return Notification::query()->create([
            'user_id' => $data->getUserId(),
            'name' => $data->getName(),
            'description' => $data->getDescription(),
            'type' => $data->getType()
        ]);
    }

}