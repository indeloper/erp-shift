<?php

declare(strict_types=1);

namespace App\Domain\Enum;

final class NotificationSortType
{

    const NAME = 'name';

    const CONTRACTOR_SHORT_NAME = 'contractor.short_name';

    const OBJECT_ADDRESS = 'object.address';

    const DATA = 'created_at';

    public static function sorts()
    {
        return [
            self::NAME,
            self::CONTRACTOR_SHORT_NAME,
            self::OBJECT_ADDRESS,
            self::DATA,
        ];
    }

}