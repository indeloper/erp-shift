<?php

namespace App\Listeners\Bitrix\Deal;

use App\Events\Bitrix\Deal\DealAddEvent;
use App\Services\Bitrix\BitrixServiceInterface;

class DealAddListener
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
    public function handle(DealAddEvent $event): void
    {
        $deal = $this->bitrixService->getDeal(
            $event->data->id
        );

        if ($deal === null) {
            return;
        }

        if (empty($deal->UF_CRM_1715933754) === false) {
            $this->bitrixService->updateProjectByBitrixDeal($deal);
        }
    }

}
