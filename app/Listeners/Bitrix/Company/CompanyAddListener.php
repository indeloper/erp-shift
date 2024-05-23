<?php

namespace App\Listeners\Bitrix\Company;

use App\Events\Bitrix\Company\CompanyAddEvent;
use App\Repositories\Contractor\ContractorRepositoryInterface;
use App\Services\Bitrix\BitrixServiceInterface;

class CompanyAddListener
{

    /**
     * Create the event listener.
     */
    public function __construct(
        public BitrixServiceInterface $bitrixService,
        public ContractorRepositoryInterface $contractorRepository
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CompanyAddEvent $event): void
    {
        $company = $this->bitrixService->getCompany(
            $event->data->id
        );

        if ($company === null || $company->requisite === null) {
            return;
        }

        $inn = $company->requisite->RQ_INN;
        $kpp = $company->requisite->RQ_KPP;

        if ($inn === null || $kpp === null) {
            return;
        }

        $contractor = $this->contractorRepository->getByInnAndKpp(
            $inn,
            $kpp
        );

        if ($contractor === null) {
            $this->bitrixService->storeERPCompany(
                $company,
            );
        }

        if ($contractor !== null) {
            $contractor->update([
                'bitrix_id' => $company->ID,
            ]);

            $this->bitrixService->updateERPCompany(
                $event->data->id
            );
        }
    }

}
