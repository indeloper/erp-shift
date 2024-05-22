<?php

declare(strict_types=1);

namespace App\Domain\Enum\Bitrix;

enum BitrixSyncType: string
{

    case Company = 'company';

    public function name()
    {
        return match ($this) {
            self::Company => 'Компании'
        };
    }

}