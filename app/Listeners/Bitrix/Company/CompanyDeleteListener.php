<?php

namespace App\Listeners\Bitrix\Company;

use App\Events\Bitrix\Company\CompanyDeleteEvent;
use App\Repositories\Contractor\ContractorRepositoryInterface;

class CompanyDeleteListener
{

    /**
     * Create the event listener.
     */
    public function __construct(
        public ContractorRepositoryInterface $contractorRepository
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CompanyDeleteEvent $event): void
    {
        $contractor = $this->contractorRepository->getByBitrixId(
            $event->data->id
        );

        if ($contractor === null) {
            return;
        }

        $contractor->update([
            'is_delete_bitrix' => true,
        ]);
    }

}
