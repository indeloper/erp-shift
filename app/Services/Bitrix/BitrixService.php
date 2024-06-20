<?php

declare(strict_types=1);

namespace App\Services\Bitrix;

use App\Domain\DTO\Bitrix\BitrixEventRequestData;
use App\Domain\DTO\Bitrix\Entity\CompanyItemData;
use App\Domain\DTO\Bitrix\Entity\DealItemData;
use App\Domain\DTO\Bitrix\Entity\RequisiteItemData;
use App\Domain\DTO\Bitrix\Entity\RequisiteListData;
use App\Domain\Enum\Bitrix\BitrixEventType;
use App\Events\Bitrix\Company\CompanyAddEvent;
use App\Events\Bitrix\Company\CompanyDeleteEvent;
use App\Events\Bitrix\Company\CompanyUpdateEvent;
use App\Events\Bitrix\Deal\DealAddEvent;
use App\Events\Bitrix\Deal\DealUpdateEvent;
use App\Models\Contractors\Contractor;
use App\Models\Project;
use App\Repositories\Contractor\ContractorRepositoryInterface;
use App\Repositories\Project\ProjectRepository;
use App\Repositories\ProjectObject\ProjectObjectRepository;
use App\Services\Project\ProjectService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

final class BitrixService implements BitrixServiceInterface
{

    public function __construct(
        private ContractorRepositoryInterface $contractorRepository,
        private ProjectService $projectService,
        private ProjectRepository $projectRepository,
        private ProjectObjectRepository $projectObjectRepository
    ) {}

    /**
     * @param  array  $filter
     *
     * @return RequisiteListData
     */
    private function getRequisiteList(array $filter = []): RequisiteListData
    {
        $response = CRest::call('crm.requisite.list', [
            'filter' => $filter,
        ]);

        $this->sleep();

        return new RequisiteListData(
            $response['result'],
            $response['total']
        );
    }

    public function getDeal(int $idDeal): ?DealItemData
    {
        $response = CRest::call('crm.deal.get', [
            'id' => $idDeal,
        ]);

        // UF_CRM_1715933754 - BITRIX-ID USER FIELD

        if (isset($response['error'])
            || isset($response['result']) === false
        ) {
            return null;
        }

        return DealItemData::make(
            $response['result'],
        );
    }

    /**
     * @param  string  $id
     *
     * @return CompanyItemData|null
     */
    public function getCompany(
        string $id,
    ): ?CompanyItemData {
        $response = CRest::call('crm.company.get', [
            'id' => $id,
        ]);

        $this->sleep();

        if (isset($response['error'])
            || isset($response['result']) === false
        ) {
            return null;
        }

        $requisiteList = $this->getRequisiteList([
            'ENTITY_TYPE_ID' => 4,
            'ENTITY_ID'      => $response['result']['ID'],
        ]);

        $requisite = null;
        if ($requisiteList->total) {
            $requisite = $requisiteList->result->last();
        }

        $result = $response['result'];

        return CompanyItemData::make([
            ...$result,
            'requisite' => $requisite,
        ]);
    }

    /**
     * @param  string  $inn
     * @param  string  $kpp
     *
     * @return CompanyItemData|null
     */
    public function getCompanyByInnAndKpp(
        string $inn,
        string $kpp
    ): ?CompanyItemData {
        $response = $this->getRequisiteList([
            'ENTITY_TYPE_ID' => 4,
            'RQ_INN'         => $inn,
            'RQ_KPP'         => $kpp,
        ]);

        if ($response->result->count() === 0) {
            return null;
        }

        return $this->getCompanyById(Arr::first($response->result)->ENTITY_ID);
    }

    /**
     * @param  BitrixEventRequestData  $data
     *
     * @return void
     */
    public function dispatchEvent(BitrixEventRequestData $data): void
    {
        $event = match ($data->event) {
            BitrixEventType::CompanyUpdate->value => CompanyUpdateEvent::class,
            BitrixEventType::CompanyAdd->value => CompanyAddEvent::class,
            BitrixEventType::CompanyDelete->value => CompanyDeleteEvent::class,
            BitrixEventType::DealUpdate->value => DealUpdateEvent::class,
            BitrixEventType::DealAdd->value => DealAddEvent::class,
            default => null,
        };

        if ($event === null) {
            return;
        }

        event(new $event($data));
    }

    /**
     * @param  CompanyItemData  $data
     *
     * @return false|int
     */
    public function storeCompany(CompanyItemData $data): false|int
    {
        $companyDataArray = $data->toArray();

        unset($companyDataArray['requisite']);

        $requisite = $data->requisite;

        $response = CRest::call('crm.company.add', [
            'fields' => $companyDataArray,
            // TODO: // НУЖНО ПЕРЕДАВАТЬ КОНКРЕТНЫЙ НАБОР ПОЛЕЙ А ТО БУДЕТ СОЗДАВАТЬСЯ НЕНУЖНЫЙ РЕКВИЗИТ КОТОРЫЙ НЕ ОБНОВЛЯЕТСЯ (
        ]);

        $this->sleep();

        if (isset($response['result']) === false) {
            Log::error('STORE COMPANY: '.$response);

            return false;
        }

        $idCompany = $response['result'];

        $requisiteList = $this->getRequisiteList([
            'ENTITY_TYPE_ID' => 4,
            'ENTITY_ID'      => $idCompany,
        ]);

        if ($requisite !== null) {
            if ($requisiteList->total) {
                $requisiteId = $requisiteList->result->last()->ID;

                $requisite->ID        = $requisiteId;
                $requisite->ENTITY_ID = (string) $idCompany;

                $this->updateRequisite(
                    $requisiteId,
                    $requisite
                );
            } else {
                $requisite->ENTITY_ID = (string) $idCompany;

                $this->storeRequisite($requisite);
            }
        }

        return $idCompany;
    }

    /**
     * @param  RequisiteItemData  $data
     *
     * @return false|int
     */
    public function storeRequisite(RequisiteItemData $data): false|int
    {
        $response = CRest::call('crm.requisite.add', [
            'fields' => [
                ...$data->toArray(),
                'REG_ADDRESS' => 'TEST',
            ],
        ]);

        $this->sleep();

        if (isset($response['result']) === false) {
            return false;
        }

        return $response['result'];
    }

    /**
     * @param  Contractor  $item
     *
     * @return false|int
     */
    public function storeCompanyByModel(Contractor $item): false|int
    {
        //CUSTOMER
        //SUPPLIER
        //COMPETITOR
        //PARTNER
        //OTHER

        $data = $this->buildCompanyDataByModal($item);

        return $this->storeCompany(
            $data
        );
    }

    /**
     * @param  string  $id
     * @param  RequisiteItemData  $requisite
     *
     * @return bool
     */
    private function updateRequisite(
        string $id,
        RequisiteItemData $requisite
    ): bool {
        $response = CRest::call('crm.requisite.update', [
            'id'     => $id,
            'fields' => [
                'NAME'    => $requisite->NAME,
                'RQ_INN'  => $requisite->RQ_INN, // Новый ИНН
                'RQ_KPP'  => $requisite->RQ_KPP, // Новый КПП
                'RQ_OGRN' => $requisite->RQ_OGRN, // Новый ОГРН
            ],
        ]);

        $this->sleep();

        return $response['result'];
    }

    private function updateDeal(string $id, DealItemData $item): bool
    {
        $response = CRest::call('crm.deal.update', [
            'id'     => $id,
            'fields' => [
                'TITLE'             => $item->TITLE,
                'UF_CRM_1715933754' => $item->UF_CRM_1715933754, // PROJECT ID
            ],
        ]);

        return $response['result'];
    }

    /**
     * @param  Contractor  $contractor
     * @param  CompanyItemData  $company
     *
     * @return bool
     */
    public function updateCompanyByModal(
        Contractor $contractor,
        CompanyItemData $company
    ): bool {
        $modalCompanyData = $this->buildCompanyDataByModal($contractor);

        $modalCompanyData->ID = $company->ID;

        $companyDataArray = $modalCompanyData->toArray();

        unset($companyDataArray['requisite']);

        $requisite = $modalCompanyData->requisite;

        $response = CRest::call('crm.company.update', [
            'id'     => $company->ID,
            'fields' => $companyDataArray,
        ]);

        $this->sleep();

        if ($response['result'] === false) {
            return false;
        }

        $requisiteList = $this->getRequisiteList([
            'ENTITY_TYPE_ID' => 4,
            'ENTITY_ID'      => $company->ID,
        ]);

        if ($requisiteList->total) {
            $requisiteId = $requisiteList->result->last()->ID;

            $requisite->ID        = $requisiteId;
            $requisite->ENTITY_ID = (string) $company->ID;

            $result = $this->updateRequisite(
                $requisiteId,
                $requisite
            );

            return $result;
        }

        $requisite->ENTITY_ID = (string) $company->ID;

        $result = $this->storeRequisite($requisite);

        return $result !== false;
    }

    /**
     * @param  Contractor  $item
     *
     * @return CompanyItemData
     */
    private function buildCompanyDataByModal(Contractor $item): CompanyItemData
    {
        return CompanyItemData::make([
            'ID'            => null,
            'COMPANY_TYPE'  => 'CUSTOMER', // DEFAULT: CUSTOMER,
            'TITLE'         => $item->short_name,
            'HAS_PHONE'     => 'N',
            'HAS_EMAIL'     => 'N',
            'HAS_IMOL'      => 'N',
            'DATE_CREATE'   => now()->toAtomString(),
            'DATE_MODIFY'   => now()->toAtomString(),
            'OPENED'        => 'Y',
            //            'ADDRESS'       => $item->physical_adress,
            //            'REG_ADDRESS'   => $item->legal_address,
            'IS_MY_COMPANY' => $item->is_client ? 'N' : 'N',
            'requisite'     => RequisiteItemData::make([
                'ID'                   => null,
                'ENTITY_TYPE_ID'       => '4',
                'ENTITY_ID'            => null,
                'PRESET_ID'            => '1',
                'DATE_CREATE'          => now()->toAtomString(),
                'NAME'                 => 'Организация',
                'ACTIVE'               => 'Y',
                'SORT'                 => '500',
                'RQ_COMPANY_NAME'      => $item->short_name,
                'RQ_COMPANY_FULL_NAME' => $item->full_name,
                'RQ_COMPANY_REG_DATE'  => null,
                'RQ_DIRECTOR'          => $item->general_manager,
                'RQ_INN'               => $item->inn,
                'RQ_KPP'               => $item->kpp,
                'RQ_IFNS'              => null,
                'RQ_OGRN'              => $item->ogrn,
            ]),
        ]);
    }

    /**
     * @param  string  $bitrixId
     *
     * @return CompanyItemData|null
     */
    public function getCompanyById(string $bitrixId): ?CompanyItemData
    {
        $company = $this->getCompany($bitrixId);

        if ($company === null) {
            return null;
        }

        return $company;
    }

    /**
     * @return void
     */
    private function sleep(): void
    {
        // BITRIX БЛОКИРУЕТ ЕСЛИ КИДАЕШЬ МНОГО ЗАПРОСОВ
        sleep(1);
    }

    /**
     * @param  string  $id
     *
     * @return bool
     */
    public function updateERPCompany(string $id): bool
    {
        $bitrixCompany = $this->getCompany($id);

        if ($bitrixCompany === null) {
            return false;
        }

        $contractor = $this->contractorRepository
            ->getByBitrixId($bitrixCompany->ID);

        if ($contractor === null) {
            return false;
        }

        $contractor->update([
            'full_name'       => $bitrixCompany->requisite->RQ_COMPANY_FULL_NAME,
            'short_name'      => $bitrixCompany->TITLE,
            'inn'             => $bitrixCompany->requisite->RQ_INN,
            'kpp'             => $bitrixCompany->requisite->RQ_KPP,
            'ogrn'            => $bitrixCompany->requisite->RQ_OGRN,
            'general_manager' => $bitrixCompany->requisite->RQ_DIRECTOR,
        ]);

        return true;
    }

    /**
     * @param  CompanyItemData  $bitrixCompany
     *
     * @return Contractor
     */
    public function storeERPCompany(CompanyItemData $bitrixCompany): Contractor
    {
        return Contractor::query()->create([
            'bitrix_id'       => $bitrixCompany->ID,
            'full_name'       => $bitrixCompany->requisite->RQ_COMPANY_FULL_NAME
                ?? 'FULL NAME',
            'short_name'      => $bitrixCompany->TITLE ?? 'SHORT NAME',
            'inn'             => $bitrixCompany->requisite->RQ_INN,
            'kpp'             => $bitrixCompany->requisite->RQ_KPP,
            'ogrn'            => $bitrixCompany->requisite->RQ_OGRN,
            'general_manager' => $bitrixCompany->requisite->RQ_DIRECTOR,
        ]);
    }

    public function storeProjectByBitrixDeal(DealItemData $deal): Project
    {
        $newProject = $this->projectService->store(
            $deal->TITLE,
            status: true
        );

        $newProject->objects()->create([
            'bitrix_id' => $deal->ID,
            'name'      => $deal->TITLE,
        ]);

        $deal->UF_CRM_1715933754 = (string) $newProject->id;

        $this->updateDeal($deal->ID, $deal);

        return $newProject;
    }

    public function updateProjectByBitrixDeal(DealItemData $deal): ?Project
    {
        $projectObject = $this->projectObjectRepository
            ->getProjectObjectByBitrixId((int) $deal->ID);

        $project = $this->projectRepository->getProjectById(
            (int) $deal->UF_CRM_1715933754,
        );

        if ($project === null) {
            return null;
        }

        if ($projectObject === null) {
            $project->objects()->create([
                'bitrix_id' => $deal->ID,
                'name'      => $deal->TITLE,
            ]);
        }

        if ($projectObject !== null) {
            // ЧТО-ТО ТУТ
        }

        return $project;
    }

}