<?php

declare(strict_types=1);

namespace App\Domain\Enum;

final class NotificationChannelType
{
    const MAIL = 'mail';

    const TELEGRAM = 'telegram';

    const SYSTEM = 'system';

    public static function values(): array
    {
        return [
            self::MAIL,
            self::TELEGRAM,
            self::SYSTEM,
        ];
    }
}
