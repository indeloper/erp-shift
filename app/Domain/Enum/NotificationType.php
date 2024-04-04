<?php

declare(strict_types=1);

namespace App\Domain\Enum;

use App\Notifications\DefaultNotification;
use App\Notifications\OnlyTelegramNotification;

final class NotificationType
{
    const DEFAULT = 0;
    const ONLY_TELEGRAM = 1;

    public static function determinateNotificationClassByType(int $type): string
    {
        switch ($type) {
            case NotificationType::ONLY_TELEGRAM:
                return OnlyTelegramNotification::class;
            case NotificationType::DEFAULT:
            default:
                return DefaultNotification::class;
        }
    }
}