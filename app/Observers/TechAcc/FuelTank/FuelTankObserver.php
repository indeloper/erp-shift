<?php

namespace App\Observers\TechAcc\FuelTank;

use App\Models\TechAcc\FuelTank\FuelTank;
use Illuminate\Support\Facades\Auth;

class FuelTankObserver
{
    // deleting закомментировал Антон. По идее записи в журнале операций должны сохраняться
    // public function deleting(FuelTank $fuelTank)
    // {
    //     $fuelTank->operations->each(function($operation) {$operation->delete();});
    // }

    //    /**
    //     * Handle the fuel tank "updated" event.
    //     *
    //     * @param  \App\Models\TechAcc\FuelTank\FuelTank  $fuelTank
    //     * @return void
    //     */
    //    public function updating(FuelTank $fuelTank)
    //    {
    //        if ($fuelTank->isDirty('fuel_level')) {
    //            $fuelTank->operations()->create([
    //                'author_id' => Auth::user()->id,
    //                'object_id' => $fuelTank->object_id,
    //                'value' => ($fuelTank->fuel_level - $fuelTank->getOriginal('fuel_level')),
    //                'type' => 3,
    //                'description' => 'Ручное изменение уровня топлива',
    //                'operation_date' => now(),
    //            ]);
    //        }
    //    }
}
