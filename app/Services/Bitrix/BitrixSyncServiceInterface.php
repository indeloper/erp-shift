<?php

declare(strict_types=1);

namespace App\Services\Bitrix;

use App\Domain\Enum\Bitrix\BitrixSyncDirectionType;
use App\Domain\Enum\Bitrix\BitrixSyncType;
use App\Services\Bitrix\Sync\BitrixSyncHandlerInterface;

interface BitrixSyncServiceInterface
{

    /**
     * @param  BitrixSyncType  $type
     * @param  BitrixSyncDirectionType  $direction
     *
     * @return BitrixSyncHandlerInterface
     */
    public function defineSyncHandler(
        BitrixSyncType $type,
        BitrixSyncDirectionType $direction
    ): BitrixSyncHandlerInterface;

}