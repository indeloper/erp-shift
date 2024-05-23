<?php

namespace App\Observers;

use App\Models\Contractors\Contractor;
use App\Services\Bitrix\Sync\Bitrix\BitrixCompanySyncHandler;

class ContractorObserver
{

    /**
     * Handle the Contractor "created" event.
     */
    public function created(Contractor $contractor): void
    {
        BitrixCompanySyncHandler::syncStatic(
            $contractor
        );
    }

    /**
     * Handle the Contractor "updated" event.
     */
    public function updated(Contractor $contractor): void
    {
        BitrixCompanySyncHandler::syncStatic(
            $contractor
        );
    }

}
