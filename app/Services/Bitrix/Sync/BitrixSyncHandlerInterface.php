<?php

declare(strict_types=1);

namespace App\Services\Bitrix\Sync;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\LazyCollection;

interface BitrixSyncHandlerInterface
{

    public function collection(): Collection|SupportCollection|LazyCollection;

    public function sync($item): void;

}