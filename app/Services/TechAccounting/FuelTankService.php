<?php
namespace App\Services\TechAccounting;

use App\Models\TechAcc\FuelTank\FuelTankOperation;
use Carbon\Carbon;

class FuelTankService
{
    public static function guardAgainstNegativeValue(FuelTankOperation $fuelTankOperation, $operation_value = null, $fuel_level = 0)
    {
        $fuel_level = $fuel_level ? $fuel_level : $fuelTankOperation->fuel_tank()->first()->fuel_level;
        $operation_value = $operation_value ? $operation_value : $fuelTankOperation->value_diff;

        $fuel_level += $operation_value;
        $is_date_legal = $fuelTankOperation->opertion_date >= $fuelTankOperation->fuel_tank()->first()->exploitation_start;
        if ($fuel_level <= 0 and $is_date_legal) {
            abort(422, 'В топливной ёмкости меньше топлива, чем требуется');
        }

        $future_operations = $fuelTankOperation->future_history;
        foreach ($future_operations as $operation) {
            $operation_value = $operation->value_diff;

            $fuel_level += $operation_value;
            if ($fuel_level <= 0) {
                abort(422, 'В топливной ёмкости меньше топлива, чем требуется');
            }
        }

        return true;
    }

    public static function createHistory(FuelTankOperation $fuelTankOperation)
    {
        $new_values = $fuelTankOperation->getDirty();
        $old_values = [];

        foreach ($new_values as $field => $value) {
            if ($field == 'operation_date' and self::dateWasNotChanged($fuelTankOperation)) {
                unset($new_values['operation_date']);
            }
            else {
                $old_values[$field] = $fuelTankOperation->getOriginal($field);
            }
        }

        $fuelTankOperation->history()->create([
            'changed_fields' => [
                'old_values' => $old_values,
                'new_values' => $new_values,
            ],
            'user_id' => auth()->id(),
        ]);
    }

    public static function dateWasNotChanged(FuelTankOperation $fuelTankOperation)
    {
        if ($fuelTankOperation->isDirty('operation_date')) {
            return Carbon::parse($fuelTankOperation->getDirty()['operation_date'])->isSameDay($fuelTankOperation->getOriginal('operation_date'));
        }
        return true;
    }
}
