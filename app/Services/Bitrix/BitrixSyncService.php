<?php

declare(strict_types=1);

namespace App\Services\Bitrix;

use App\Domain\Enum\Bitrix\BitrixSyncDirectionType;
use App\Domain\Enum\Bitrix\BitrixSyncType;
use App\Services\Bitrix\Sync\Bitrix\BitrixCompanySyncHandler;
use App\Services\Bitrix\Sync\BitrixSyncHandlerInterface;

final class BitrixSyncService implements BitrixSyncServiceInterface
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
    ): BitrixSyncHandlerInterface {
        if ($direction === BitrixSyncDirectionType::Bitrix) {
            if ($type === BitrixSyncType::Company) {
                return app(BitrixCompanySyncHandler::class);
            }
        }
    }

}