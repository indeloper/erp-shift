<?php

declare(strict_types=1);

namespace App\Services\Bitrix;

use App\Domain\DTO\Bitrix\BitrixEventRequestData;
use App\Domain\DTO\Bitrix\Entity\CompanyItemData;
use App\Models\Contractors\Contractor;

interface BitrixServiceInterface
{

    /**
     * @param  string  $inn
     * @param  string  $kpp
     *
     * @return CompanyItemData|null
     */
    public function getCompanyByInnAndKpp(
        string $inn,
        string $kpp
    ): ?CompanyItemData;

    /**
     * @param  BitrixEventRequestData  $data
     *
     * @return void
     */
    public function dispatchEvent(BitrixEventRequestData $data): void;

    /**
     * @param  CompanyItemData  $data
     *
     * @return mixed
     */
    public function storeCompany(CompanyItemData $data): mixed;

    /**
     * @param  Contractor  $item
     *
     * @return false|int
     */
    public function storeCompanyByModel(Contractor $item): false|int;

    /**
     * @param  Contractor  $contractor
     * @param  CompanyItemData  $company
     *
     * @return bool
     */
    public function updateCompanyByModal(
        Contractor $contractor,
        CompanyItemData $company
    ): bool;

    /**
     * @param  string  $bitrixId
     *
     * @return CompanyItemData|null
     */
    public function getCompanyById(string $bitrixId): ?CompanyItemData;

    public function updateERPCompany(string $id): bool;

}