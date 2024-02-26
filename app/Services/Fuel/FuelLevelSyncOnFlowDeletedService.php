<?php

namespace App\Services\Fuel;

use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankFlow;
use App\Models\TechAcc\FuelTank\FuelTankFlowType;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;

class FuelLevelSyncOnFlowDeletedService
{
    protected $fuelTankTransferHistory;
    protected $finalFuelLevelAmount;

    public function __construct($fuelTankTransferHistory)
    {
        $this->fuelTankTransferHistory = $fuelTankTransferHistory;
        $this->handle();
    }

    public function handle()
    {
        $this->updateChainOfFuelTankTransferHistories();
        $this->updateFuelTankFuelLevel();
    }

    public function updateChainOfFuelTankTransferHistories()
    {
        $currentFuelTankTransferHistory = $this->fuelTankTransferHistory;
        $parent = FuelTankTransferHistory::find($currentFuelTankTransferHistory->parent_fuel_level_id);
        $parentFuelLevel = $parent->fuel_level ?? 0;
        $parentId = $parent->id ?? null;

        $i=0;
        while($currentFuelTankTransferHistory) { 
            if($i===0) {
                if(FuelTankTransferHistory::where('parent_fuel_level_id', $this->fuelTankTransferHistory->id)->first()) {
                    $currentFuelTankTransferHistory = FuelTankTransferHistory::where('parent_fuel_level_id', $this->fuelTankTransferHistory->id)->first();
                    $currentFuelTankTransferHistory->parent_fuel_level_id = $parentId;
                } else {
                    $this->finalFuelLevelAmount = $parent->fuel_level;
                    break;
                }
            } else {
                $currentFuelTankTransferHistory = FuelTankTransferHistory::where('parent_fuel_level_id', $parentId)->first();
            }
            
            if($currentFuelTankTransferHistory) {
                $currentFuelTankTransferHistory->fuel_level = $this->getFuelLevel($currentFuelTankTransferHistory, $parentFuelLevel);
                $currentFuelTankTransferHistory->save();

                $parentFuelLevel = $currentFuelTankTransferHistory->fuel_level;
                $parentId = $currentFuelTankTransferHistory->id;
                $this->finalFuelLevelAmount = $currentFuelTankTransferHistory->fuel_level;
            }

            $i++;
        }
    }

    public function updateFuelTankFuelLevel()
    {
        $fuelTank = FuelTank::find($this->fuelTankTransferHistory->fuel_tank_id);
        $fuelTank->fuel_level = $this->finalFuelLevelAmount;
        $fuelTank->save();
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