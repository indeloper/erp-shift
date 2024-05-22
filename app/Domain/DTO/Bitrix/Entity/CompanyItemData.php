<?php

declare(strict_types=1);

namespace App\Domain\DTO\Bitrix\Entity;

use App\Domain\DTO\DTO;

final class CompanyItemData extends DTO
{

    public function __construct(
        public ?string $ID = null,
        public ?string $COMPANY_TYPE = null,
        public ?string $TITLE = null,
        public ?string $HAS_PHONE = null,
        public ?string $HAS_EMAIL = null,
        public ?string $HAS_IMOL = null,
        public ?string $DATE_CREATE = null,
        public ?string $DATE_MODIFY = null,
        public ?string $OPENED = null,
        public ?string $IS_MY_COMPANY = null,
        //        public ?string $ADDRESS = null,
        //        public ?string $REG_ADDRESS = null,
        public ?RequisiteItemData $requisite = null,
    ) {}

}