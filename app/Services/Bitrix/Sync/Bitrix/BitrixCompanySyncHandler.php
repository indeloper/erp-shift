<?php

declare(strict_types=1);

namespace App\Services\Bitrix\Sync\Bitrix;

use App\Models\Contractors\Contractor;
use App\Services\Bitrix\Sync\BitrixSyncBase;
use App\Services\Bitrix\Sync\BitrixSyncHandlerInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\LazyCollection;

class BitrixCompanySyncHandler extends BitrixSyncBase
    implements BitrixSyncHandlerInterface
{

    public function collection(): Collection|SupportCollection|LazyCollection
    {
        return Contractor::query()
            ->where('is_delete_bitrix', false)
            ->whereNotNull('inn')
            ->whereNotNull('kpp')
            ->lazy();
    }

    /**
     * @param  Contractor  $item
     *
     * @return void
     */
    public function sync($item): void
    {
        if ($item->bitrix_id === null) {
            $company = $this->bitrixService->getCompanyByInnAndKpp(
                $item->inn,
                $item->kpp
            );
        } else {
            $company = $this->bitrixService->getCompanyById(
                (string) $item->bitrix_id,
            );
        }

        if ($company === null) {
            $storeCompanyResult
                = $this->bitrixService->storeCompanyByModel($item);

            if ($storeCompanyResult !== false) {
                $item
                    ->updateQuietly([
                        'bitrix_id' => $storeCompanyResult,
                    ]);
            }
        }

        if ($company !== null) {
            $this->bitrixService->updateCompanyByModal($item,
                $company);

            $item->updateQuietly([
                'bitrix_id' => $company->ID,
            ]);
        }
    }

}