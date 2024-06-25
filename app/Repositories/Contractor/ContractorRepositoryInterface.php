<?php

declare(strict_types=1);

namespace App\Repositories\Contractor;

use App\Models\Contractors\Contractor;

interface ContractorRepositoryInterface
{

    public function getByBitrixId(string $bitrixId): ?Contractor;

    public function getByInnAndKpp(string $RQ_INN, string $RQ_KPP): ?Contractor;

}