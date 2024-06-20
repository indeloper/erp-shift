<?php

declare(strict_types=1);

namespace App\Domain\Enum\Bitrix;

enum BitrixEventType: string
{

    case TaskUpdate = 'ONTASKUPDATE';
    case RequisiteUpdate = 'ONCRMREQUISITEUPDATE';
    case CompanyUpdate = 'ONCRMCOMPANYUPDATE';
    case CompanyAdd = 'ONCRMCOMPANYADD';
    case CompanyDelete = 'ONCRMCOMPANYDELETE';
    case DealUpdate = 'ONCRMDEALUPDATE';

    case DealAdd = 'ONCRMDEALADD';

}