<?php

declare(strict_types=1);

namespace App\Domain\DTO\Bitrix\Entity;

use App\Domain\DTO\DTO;

class RequisiteItemData extends DTO
{

    public function __construct(
        public ?string $ID = null,
        public ?string $ENTITY_TYPE_ID = null,
        public ?string $ENTITY_ID = null,
        public ?string $PRESET_ID = null,
        public ?string $DATE_CREATE = null,
        public ?string $NAME = null,
        public ?string $ACTIVE = null,
        public ?string $SORT = null,
        public ?string $RQ_COMPANY_NAME = null,
        public ?string $RQ_COMPANY_FULL_NAME = null,
        public ?string $RQ_COMPANY_REG_DATE = null,
        public ?string $RQ_DIRECTOR = null,
        public ?string $RQ_INN = null,
        public ?string $RQ_KPP = null,
        public ?string $RQ_IFNS = null,
        public ?string $RQ_OGRN = null,
    ) {}

}