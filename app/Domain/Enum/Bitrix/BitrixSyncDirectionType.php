<?php

declare(strict_types=1);

namespace App\Domain\Enum\Bitrix;

enum BitrixSyncDirectionType: string
{

    case Bitrix = 'bitrix';
    case Erp = 'erp';

    public function name()
    {
        return match ($this) {
            self::Bitrix => 'Bitrix 24',
            self::Erp => 'ERP'
        };
    }

}