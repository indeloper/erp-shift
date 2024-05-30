<?php

namespace App\Observers;

use App\Models\TechAcc\Vehicles\VehicleCategoryCharacteristics;

class VehicleCategoryCharacteristicObserver
{
    /**
     * Handle the vehicle category characteristics "updated" event.
     */
    public function updated(VehicleCategoryCharacteristics $vehicleCategoryCharacteristics): void
    {
        $vehicleCategoryCharacteristics->parameters()->update(['value' => '']);
    }

    /**
     * Handle the vehicle category characteristics "deleted" event.
     */
    public function deleting(VehicleCategoryCharacteristics $vehicleCategoryCharacteristics): void
    {
        $vehicleCategoryCharacteristics->parameters()->get()->each(function ($item) {
            $item->delete();
        });
    }

    /**
     * Handle the vehicle category characteristics "restored" event.
     */
    public function restored(VehicleCategoryCharacteristics $vehicleCategoryCharacteristics): void
    {
        //
    }
}
