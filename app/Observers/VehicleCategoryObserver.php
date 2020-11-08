<?php

namespace App\Observers;

use App\Models\TechAcc\Vehicles\VehicleCategories;

class VehicleCategoryObserver
{
    /**
     * Handle the vehicle categories "deleted" event.
     *
     * @param  VehicleCategories  $vehicleCategories
     * @return void
     */
    public function deleting(VehicleCategories $vehicleCategories)
    {
//        $vehicleCategories->characteristics()->get()->each(function ($item) { $item->delete(); });
        $vehicleCategories->vehicles()->get()->each(function ($item) { $item->delete(); });
    }

    /**
     * Handle the vehicle categories "restored" event.
     *
     * @param  VehicleCategories  $vehicleCategories
     * @return void
     */
    public function restored(VehicleCategories $vehicleCategories)
    {
        //
    }
}
