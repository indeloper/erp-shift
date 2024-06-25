<?php

declare(strict_types=1);

namespace App\Services\Bitrix;

use App\Domain\DTO\Bitrix\BitrixEventRequestData;
use App\Domain\DTO\Bitrix\Entity\CompanyItemData;
use App\Domain\DTO\Bitrix\Entity\DealItemData;
use App\Models\Contractors\Contractor;
use App\Models\Project;
use App\Models\ProjectObject;

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

    /**
     * @param  string  $id
     *
     * @return bool
     */
    public function updateERPCompany(string $id): bool;

    /**
     * @param  CompanyItemData  $bitrixCompany
     *
     * @return Contractor
     */
    public function storeERPCompany(CompanyItemData $bitrixCompany): Contractor;

    public function getDeal(int $idDeal): ?DealItemData;

    public function storeProjectByBitrixDeal(DealItemData $deal): Project;

    public function updateProjectByBitrixDeal(DealItemData $deal): ?Project;

    public function updateDealByModal(ProjectObject $projectObject): void;

}