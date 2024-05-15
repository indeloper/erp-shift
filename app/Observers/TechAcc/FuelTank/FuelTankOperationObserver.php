<?php

namespace App\Observers\TechAcc\FuelTank;

use App\Jobs\ProcessFuelTankOperation;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankOperation;
use App\Services\TechAccounting\FuelTankService;

class FuelTankOperationObserver
{
    /**
     * Handle the fuel tank operation "creating" event.
     *
     * @return void
     */
    public function creating(FuelTankOperation $fuelTankOperation): void
    {
        if (auth()->id()) {
            $fuelTankOperation->author_id = auth()->id();
        }
        FuelTankService::guardAgainstNegativeValue($fuelTankOperation, null, $fuelTankOperation->old_fuel_level);

        ProcessFuelTankOperation::dispatchSync($fuelTankOperation);
    }

    /**
     * Handle the fuel tank operation "updating" event.
     *
     * @return void
     */
    public function updating(FuelTankOperation $fuelTankOperation): void
    {
        $fields_to_reculc = [
            'fuel_tank_id',
            'value',
            'type',
            'operation_date',
            'result_value',
        ];
        if (count(array_intersect(array_keys($fuelTankOperation->getDirty()), $fields_to_reculc)) != 0) {
            $old_value = $fuelTankOperation->getOriginal('value');
            if ($fuelTankOperation->type == 2) {
                $old_value = -$old_value;
            }
            $value_change = $fuelTankOperation->value_diff - $old_value;
            FuelTankService::guardAgainstNegativeValue($fuelTankOperation, $value_change, $fuelTankOperation->old_fuel_level);

            if ($fuelTankOperation->isDirty('fuel_tank_id')) {
                $old_tank = FuelTank::find($fuelTankOperation->getOriginal('fuel_tank_id'));
                $old_tank->fuel_level -= $old_value;
                $old_tank->save();

                ProcessFuelTankOperation::dispatchSync($fuelTankOperation, null, FuelTank::find($fuelTankOperation->fuel_tank_id));
            } else {
                ProcessFuelTankOperation::dispatchSync($fuelTankOperation, $value_change);
            }
        }
        FuelTankService::createHistory($fuelTankOperation);
    }

    public function deleting(FuelTankOperation $fuelTankOperation)
    {
        FuelTankService::guardAgainstNegativeValue($fuelTankOperation, -$fuelTankOperation->value_diff);
        ProcessFuelTankOperation::dispatchSync($fuelTankOperation, -$fuelTankOperation->value_diff);
    }

    public function deleted(FuelTankOperation $fuelTankOperation): void
    {
        FuelTankService::createHistory($fuelTankOperation);
    }

    public function restoring(FuelTankOperation $fuelTankOperation)
    {
        ProcessFuelTankOperation::dispatchSync($fuelTankOperation);
    }
}
