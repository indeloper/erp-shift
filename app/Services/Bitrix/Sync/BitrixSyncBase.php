<?php

declare(strict_types=1);

namespace App\Services\Bitrix\Sync;

use App\Services\Bitrix\BitrixServiceInterface;

class BitrixSyncBase
{

    public function __construct(
        protected BitrixServiceInterface $bitrixService
    ) {}

    /**
     * @param $item
     *
     * @return void
     */
    public static function syncStatic($item): void
    {
        app(static::class)->sync($item);
    }

}