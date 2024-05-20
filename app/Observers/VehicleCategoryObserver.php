<?php

namespace App\Observers;

use App\Models\TechAcc\Vehicles\VehicleCategories;

class VehicleCategoryObserver
{
    /**
     * Handle the vehicle categories "deleted" event.
     */
    public function deleting(VehicleCategories $vehicleCategories): void
    {
        //        $vehicleCategories->characteristics()->get()->each(function ($item) { $item->delete(); });
        $vehicleCategories->vehicles()->get()->each(function ($item) {
            $item->delete();
        });
    }

    /**
     * Handle the vehicle categories "restored" event.
     */
    public function restored(VehicleCategories $vehicleCategories): void
    {
        //
    }
}
