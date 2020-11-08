<?php

namespace App\Observers;

use App\Models\TechAcc\Vehicles\VehicleCategoryCharacteristics;

class VehicleCategoryCharacteristicObserver
{
    /**
     * Handle the vehicle category characteristics "updated" event.
     *
     * @param  VehicleCategoryCharacteristics  $vehicleCategoryCharacteristics
     * @return void
     */
    public function updated(VehicleCategoryCharacteristics $vehicleCategoryCharacteristics)
    {
        $vehicleCategoryCharacteristics->parameters()->update(['value' => '']);
    }

    /**
     * Handle the vehicle category characteristics "deleted" event.
     *
     * @param  VehicleCategoryCharacteristics  $vehicleCategoryCharacteristics
     * @return void
     */
    public function deleting(VehicleCategoryCharacteristics $vehicleCategoryCharacteristics)
    {
        $vehicleCategoryCharacteristics->parameters()->get()->each(function ($item) { $item->delete(); });
    }

    /**
     * Handle the vehicle category characteristics "restored" event.
     *
     * @param  VehicleCategoryCharacteristics  $vehicleCategoryCharacteristics
     * @return void
     */
    public function restored(VehicleCategoryCharacteristics $vehicleCategoryCharacteristics)
    {
        //
    }
}
