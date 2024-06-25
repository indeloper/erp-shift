<?php

namespace App\Listeners\Bitrix\Requisite;

use App\Events\Bitrix\Requisite\RequisiteUpdateEvent;
use App\Services\Bitrix\BitrixServiceInterface;

class RequisiteUpdateListener
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
    public function handle(RequisiteUpdateEvent $event): void
    {
        $this->bitrixService->updateERPCompanyByRequisite(
            (string) $event->data->id
        );
    }

}
