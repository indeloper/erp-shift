<?php

declare(strict_types=1);

namespace App\Domain\Enum\Bitrix;

enum BitrixDealStageType: string
{

    case New = 'C5:NEW';
    case PrepaymentInvoice = 'C5:PREPAYMENT_INVOICE';
    case Lose = 'C5:LOSE';
    case Apology = 'C5:APOLOGY';

}