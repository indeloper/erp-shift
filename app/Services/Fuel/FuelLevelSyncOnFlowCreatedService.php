<?php

namespace App\Services\Fuel;

use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankFlow;
use App\Models\TechAcc\FuelTank\FuelTankFlowType;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;

class FuelLevelSyncOnFlowCreatedService
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
        $this->setFuelTankTransferHistoryData();
        $this->updateChainOfFuelTankTransferHistories();
        $this->updateFuelTankFuelLevel();
    }

    public function setFuelTankTransferHistoryData()
    {
        $parentFuelTankTransferHistory = $this->getParentFuelTankTransferHistory();
                
        if($parentFuelTankTransferHistory) {
            $this->fuelTankTransferHistory->parent_fuel_level_id = $parentFuelTankTransferHistory->id;
            $this->fuelTankTransferHistory->fuel_level = $this->getFuelLevel($this->fuelTankTransferHistory, $parentFuelTankTransferHistory->fuel_level);
        } else {
            $this->fuelTankTransferHistory->parent_fuel_level_id = null;
            $this->fuelTankTransferHistory->fuel_level = 0;
        }
        
        $this->fuelTankTransferHistory->save();
    }

    public function updateChainOfFuelTankTransferHistories()
    {
        $fuelTankTransferHistoriesToUpdate = $this->getFuelTankTransferHistoriesToUpdate();
        $parentFuelLevelId = $this->fuelTankTransferHistory->id;
        $fuelLevel = $this->fuelTankTransferHistory->fuel_level;

        foreach($fuelTankTransferHistoriesToUpdate as $currentFuelTankTransferHistory) {
            $currentFuelTankTransferHistory->parent_fuel_level_id = $parentFuelLevelId;
            $currentFuelTankTransferHistory->fuel_level = $this->getFuelLevel($currentFuelTankTransferHistory, $fuelLevel);
            $currentFuelTankTransferHistory->save();

            $parentFuelLevelId = $currentFuelTankTransferHistory->id;
            $fuelLevel = $currentFuelTankTransferHistory->fuel_level;
        }

        $this->finalFuelLevelAmount = $fuelLevel;
    }

    public function updateFuelTankFuelLevel()
    {
        $fuelTank = FuelTank::find($this->fuelTankTransferHistory->fuel_tank_id);
        $fuelTank->fuel_level = $this->finalFuelLevelAmount;
        $fuelTank->save();
    }

    public function getParentFuelTankTransferHistory()
    { 
        return  FuelTankTransferHistory::where([
            ['fuel_tank_id', $this->fuelTankTransferHistory->fuel_tank_id],
            ['event_date', '<=', $this->fuelTankTransferHistory->event_date],
            ['id', '<>', $this->fuelTankTransferHistory->id]
        ])
        // ->orderByDesc('id')
        ->orderByDesc('event_date')
        ->orderByDesc('parent_fuel_level_id')
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