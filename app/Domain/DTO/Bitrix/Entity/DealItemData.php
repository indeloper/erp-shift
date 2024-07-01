<?php

declare(strict_types=1);

namespace App\Domain\DTO\Bitrix\Entity;

use App\Domain\DTO\DTO;
use App\Domain\ProjectObject\DirectionWork;

final class DealItemData extends DTO
{

    public function __construct(
        public ?string $ID,
        public ?string $TITLE,
        public ?string $TYPE_ID,
        public ?string $COMPANY_ID,
        public ?string $CONTACT_ID,
        public ?string $COMMENTS,
        public ?string $CATEGORY_ID,
        public ?string $UF_CRM_1691756640193, // СУБПОДРЯДЧИК (ЗЕМЛЯ)
        public ?string $UF_CRM_1691759896386, // АДРЕС ОБЪЕКТА
        public ?string $UF_CRM_1691759946578, // ПОЛНОЕ НАИМЕНОВАНИЕ ОБЪЕКТА
        // Доп условия по материалам (шпунт/крепление >1000 тонн, новый материал или нетипичный материал).
        public ?string $UF_CRM_1710770476014,
        public ?string $UF_CRM_1715933754, // ID (ERP)
        public ?string $UF_CRM_1715079424, // Направление работ. Сваи, Шпунт
    ) {}

    public function getDirectionWork(): ?DirectionWork
    {
        if ($this->UF_CRM_1715079424 === '765') {
            return DirectionWork::Piles;
        }

        if ($this->UF_CRM_1715079424 === '763') {
            return DirectionWork::SheetPile;
        }

        return null;
    }

}