<?php

declare(strict_types=1);

namespace App\Domain\DTO;

use App\Domain\DTO\Notification\NotificationData;

final class RenderTelegramNotificationData extends TelegramNotificationData
{
    private $pathView;

    public function __construct(
        NotificationData $notificationData,
        string $path_view
    )
    {
        $this->pathView = $path_view;

        parent::__construct($notificationData);
    }

    public function getPathView(): string
    {
        return $this->pathView;
    }

}