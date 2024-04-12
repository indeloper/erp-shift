<?php

declare(strict_types=1);

namespace App\Repositories\Notification;

use App\Domain\DTO\NotificationData;
use App\Models\Notification;

final class NotificationRepository implements NotificationRepositoryInterface
{
    public function create(NotificationData $data): Notification
    {
        $fillable = (new Notification)->getFillable();

        $notificationData = collect($data->getData())
            ->only($fillable)->toArray();

        return Notification::query()->create(
            array_merge(
                [
                    'user_id' => $data->getUserId(),
                    'name' => $data->getName(),
                    'description' => $data->getDescription(),
                    'type' => $data->getType()
                ],
                $notificationData
            )
        );
    }

}