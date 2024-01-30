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
        [$parentFuelLevelId, $fuelLevel] = $this->getParentHistoryData();
        $fuelTankTransferHistoriesToUpdate = $this->getFuelTankTransferHistoriesToUpdate($parentFuelLevelId);

        foreach($fuelTankTransferHistoriesToUpdate as $currentFuelTankTransferHistory) {
            $currentFuelTankTransferHistory->parent_fuel_level_id = $parentFuelLevelId;
            $currentFuelTankTransferHistory->fuel_level = $this->getFuelLevel($currentFuelTankTransferHistory, $fuelLevel);
            $currentFuelTankTransferHistory->save();

            $parentFuelLevelId = $currentFuelTankTransferHistory->id;
            $fuelLevel = $currentFuelTankTransferHistory->fuel_level;
        }

        $this->finalFuelLevelAmount = $fuelLevel;
    }

    public function getParentHistoryData()
    {
        if($this->fuelTankTransferHistory->parent_fuel_level_id) {
            return [
                $this->fuelTankTransferHistory->parent_fuel_level_id,
                FuelTankTransferHistory::find($this->fuelTankTransferHistory->parent_fuel_level_id)->fuel_level
            ];
        } 

        return [0, 0];
    }

    public function updateFuelTankFuelLevel()
    {
        $fuelTank = FuelTank::find($this->fuelTankTransferHistory->fuel_tank_id);
        $fuelTank->fuel_level = $this->finalFuelLevelAmount;
        $fuelTank->save();
    }


    public function getFuelTankTransferHistoriesToUpdate()
    {
        return  FuelTankTransferHistory::where([
            ['fuel_tank_id', $this->fuelTankTransferHistory->fuel_tank_id],
            ['parent_fuel_level_id', '>', $this->fuelTankTransferHistory->parent_fuel_level_id]
        ])
        ->orderBy('parent_fuel_level_id')
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