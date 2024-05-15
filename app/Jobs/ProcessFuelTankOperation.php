<?php

namespace App\Jobs;

use App\Models\TechAcc\FuelTank\FuelTankOperation;
use App\Services\TechAccounting\FuelTankService;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProcessFuelTankOperation
{
    use Dispatchable, Queueable, SerializesModels;

    /**
     * @var FuelTankOperation
     */
    private $fuelTankOperation;

    private $customValueDiff;

    private $customFuelTank;

    /**
     * Create a new job instance.
     *
     * @param  null  $customValueDiff
     * @param  null  $customFuelTank
     */
    public function __construct(FuelTankOperation $fuelTankOperation, $customValueDiff = null, $customFuelTank = null)
    {
        $this->fuelTankOperation = $fuelTankOperation;
        $this->customValueDiff = $customValueDiff ?? $fuelTankOperation->value_diff;
        $this->customFuelTank = $customFuelTank ?? $fuelTankOperation->fuel_tank()->first();

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $fuelTankOperation = $this->fuelTankOperation;
        $value_diff = $this->customValueDiff;
        $fuel_tank = $this->customFuelTank;

        //        FuelTankService::guardAgainstNegativeValue($fuelTankOperation, $value_diff);

        $sync_fuel_level = 0;
        $future_operations = $fuelTankOperation->future_history;
        if ($future_operations->count()) {
            ProcessFuelTankOperation::dispatchSync($future_operations->first(), $value_diff);
            $sync_fuel_level = $future_operations->sum('value_diff');
            $future_operations->first()->save();
        }

        $fuel_tank->fuel_level += $value_diff;
        $fuel_tank->save();

        $fuelTankOperation->result_value = $fuel_tank->fuel_level - $sync_fuel_level;
    }
}
