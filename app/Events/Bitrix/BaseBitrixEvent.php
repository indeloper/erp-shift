<?php

declare(strict_types=1);

namespace App\Events\Bitrix;

use App\Domain\DTO\Bitrix\BitrixEventRequestData;

class BaseBitrixEvent
{

    /**
     * Create a new event instance.
     */
    public function __construct(
        public BitrixEventRequestData $data,
    ) {}

}