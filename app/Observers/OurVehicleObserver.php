<?php

namespace App\Observers;

use App\Models\TechAcc\Vehicles\OurVehicles;

class OurVehicleObserver
{
    /**
     * Handle the our vehicles "deleted" event.
     */
    public function deleting(OurVehicles $ourVehicles): void
    {
        //        $ourVehicles->parameters()->get()->each(function ($item) { $item->delete(); });
        $ourVehicles->documents()->get()->each(function ($item) {
            $item->delete();
        });
    }

    /**
     * Handle the our vehicles "restored" event.
     */
    public function restored(OurVehicles $ourVehicles): void
    {
        //
    }
}
