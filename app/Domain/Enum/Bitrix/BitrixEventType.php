<?php

declare(strict_types=1);

namespace App\Domain\Enum\Bitrix;

enum BitrixEventType: string
{

    case TaskUpdate = 'ONTASKUPDATE';
    case RequisiteUpdate = 'ONCRMREQUISITEUPDATE';
    case CompanyUpdate = 'ONCRMCOMPANYUPDATE';

}