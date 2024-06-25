<?php

namespace App\Listeners\Bitrix\Deal;

use App\Events\Bitrix\Deal\DealUpdateEvent;
use App\Services\Bitrix\BitrixServiceInterface;

class DealUpdateListener
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
    public function handle(DealUpdateEvent $event): void
    {
        $deal = $this->bitrixService->getDeal(
            $event->data->id
        );

        if ($deal === null) {
            return;
        }

        if (empty($deal->UF_CRM_1715933754)) {
            // CREATE NEW PROJECT AND OBJECT
            $this->bitrixService->storeProjectByBitrixDeal($deal);
        }

        if (empty($deal->UF_CRM_1715933754) === false) {
            $this->bitrixService->updateProjectByBitrixDeal($deal);
        }
    }

}
