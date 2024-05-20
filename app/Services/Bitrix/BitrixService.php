<?php

declare(strict_types=1);

namespace App\Services\Bitrix;

use App\Domain\DTO\Bitrix\BitrixEventRequestData;
use App\Domain\Enum\Bitrix\BitrixEventType;
use App\Events\Bitrix\Task\TaskUpdateEvent;
use App\Events\TestEvent;

final class BitrixService implements BitrixServiceInterface
{

    public function dispatch(BitrixEventRequestData $data): void
    {
        if ($data->event === BitrixEventType::TaskUpdate->value) {
            event(new TaskUpdateEvent($data));
        }
    }

}