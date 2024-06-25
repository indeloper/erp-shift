<?php

declare(strict_types=1);

namespace App\Domain\DTO\Bitrix;

final class BitrixEventRequestData
{

    public function __construct(
        public string $event,
        public int $eventId,
        public int $id
    ) {}

}