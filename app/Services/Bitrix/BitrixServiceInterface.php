<?php

declare(strict_types=1);

namespace App\Services\Bitrix;

use App\Domain\DTO\Bitrix\BitrixEventRequestData;

interface BitrixServiceInterface
{

    public function dispatch(BitrixEventRequestData $data): void;

}