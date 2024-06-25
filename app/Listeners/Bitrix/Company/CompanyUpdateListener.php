<?php

namespace App\Listeners\Bitrix\Company;

use App\Events\Bitrix\Company\CompanyUpdateEvent;
use App\Services\Bitrix\BitrixServiceInterface;

class CompanyUpdateListener
{

    /**
     * Create the event listener.
     */
    public function __construct(
        public BitrixServiceInterface $bitrixService
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CompanyUpdateEvent $event): void
    {
        $this->bitrixService->updateERPCompany(
            (string) $event->data->id
        );
    }

}
