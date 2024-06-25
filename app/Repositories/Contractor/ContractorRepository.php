<?php

declare(strict_types=1);

namespace App\Repositories\Contractor;

use App\Models\Contractors\Contractor;

final class ContractorRepository implements ContractorRepositoryInterface
{

    /**
     * @param  string  $bitrixId
     *
     * @return Contractor|null
     */
    public function getByBitrixId(string $bitrixId): ?Contractor
    {
        return Contractor::query()
            ->where('is_delete_bitrix', false)
            ->where('bitrix_id', $bitrixId)
            ->first();
    }

    /**
     * @param  string  $inn
     * @param  string  $kpp
     *
     * @return Contractor|null
     */
    public function getByInnAndKpp(string $inn, string $kpp): ?Contractor
    {
        return Contractor::query()
            ->where('is_delete_bitrix', false)
            ->where('inn', $inn)
            ->where('kpp', $kpp)
            ->first();
    }

}