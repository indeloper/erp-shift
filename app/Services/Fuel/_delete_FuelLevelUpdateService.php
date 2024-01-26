<?php

namespace App\Services\Fuel;

use App\Models\TechAcc\FuelTank\FuelTankFlow;
use App\Models\TechAcc\FuelTank\FuelTankFlowType;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;

class FuelLevelUpdateService
{
    protected $fuelTankTransferHistory;

    public function __construct($fuelTankTransferHistory)
    {
        $this->fuelTankTransferHistory = $fuelTankTransferHistory;
        $this->updateFuelTankTransferHistories();
    }

    public function updateFuelTankTransferHistories()
    {
        $parentFuelTankTransferHistory = $this->getParentFuelTankTransferHistory();
        $fuelTankTransferHistoriesToUpdate = $this->getFuelTankTransferHistoriesToUpdate();

        $this->fuelTankTransferHistory->parent_fuel_level_id = $parentFuelTankTransferHistory->id;
        $this->fuelTankTransferHistory->fuel_level = $this->getFuelLevel($this->fuelTankTransferHistory, $parentFuelTankTransferHistory->fuel_level ?? 0);
        $this->fuelTankTransferHistory->save();

        $parentFuelLevelId = $this->fuelTankTransferHistory->id;
        $fuelLevel = $this->fuelTankTransferHistory->fuel_level;

        foreach($fuelTankTransferHistoriesToUpdate as $currentFuelTankTransferHistory) {
            $currentFuelTankTransferHistory->parent_fuel_level_id = $parentFuelLevelId;
            $currentFuelTankTransferHistory->fuel_level = $this->getFuelLevel($currentFuelTankTransferHistory, $fuelLevel);
            $currentFuelTankTransferHistory->save();

            $parentFuelLevelId = $currentFuelTankTransferHistory->id;
            $fuelLevel = $currentFuelTankTransferHistory->fuel_level;
        }
    }

    public function getParentFuelTankTransferHistory()
    { 
        return  FuelTankTransferHistory::where([
            ['fuel_tank_id', $this->fuelTankTransferHistory->fuel_tank_id],
            ['event_date', '<=', $this->fuelTankTransferHistory->event_date],
            ['id', '<>', $this->fuelTankTransferHistory->id]
        ])
        ->orderByDesc('id')
        ->first();
    }

    public function getFuelTankTransferHistoriesToUpdate()
    {
        return  FuelTankTransferHistory::where([
            ['fuel_tank_id', $this->fuelTankTransferHistory->fuel_tank_id],
            ['event_date', '>', $this->fuelTankTransferHistory->event_date],
            ['id', '<>', $this->fuelTankTransferHistory->id]
        ])
        ->orderBy('event_date')
        ->orderBy('id')
        ->get();
    }

    public function getFuelLevel($fuelTankTransferHistory, $currentFuelLevel)
    {
        $fuelTankFlow = FuelTankFlow::find($fuelTankTransferHistory->fuel_tank_flow_id);
        
        if(!$fuelTankFlow) {
            return $currentFuelLevel;
        }

        if(FuelTankFlowType::find($fuelTankFlow->fuel_tank_flow_type_id)->slug === 'outcome') {
            return round($currentFuelLevel - $fuelTankFlow->volume);
        }

        if(FuelTankFlowType::find($fuelTankFlow->fuel_tank_flow_type_id)->slug === 'income') {
            return round($currentFuelLevel + $fuelTankFlow->volume);
        }

        if(FuelTankFlowType::find($fuelTankFlow->fuel_tank_flow_type_id)->slug === 'adjustment') {
            return round($currentFuelLevel + $fuelTankFlow->volume);
        }
    }

}