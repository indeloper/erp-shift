<?php

namespace App\Services\Fuel;

use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankFlow;
use App\Models\TechAcc\FuelTank\FuelTankFlowType;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use Illuminate\Support\Facades\Auth;

class FuelLevelSyncOnFlowUpdatedService
{
    protected $fuelTankTransferHistory;
    protected $updatedData;
    protected $fuelVolumeDiff;
    protected $finalFuelLevelAmount;

    public function __construct($fuelTankTransferHistory, $updatedData)
    {
        $this->fuelTankTransferHistory = $fuelTankTransferHistory;
        $this->updatedData = $updatedData;

        $this->handle();
    }

    public function handle()
    {
        $fuelFlow = FuelTankFlow::find($this->fuelTankTransferHistory->fuel_tank_flow_id);
        $this->fuelVolumeDiff = $this->updatedData['volume'] - $fuelFlow->volume ?? 0;

        if($this->fuelTankTransferHistory->fuel_tank_id != $this->updatedData['fuel_tank_id'])
        {
            $this->handleFuelTankChangedCase();
        }

        $fuelFlow = FuelTankFlow::find($this->updatedData['id']);

        if($fuelFlow->volume != $this->updatedData['volume']) {
            $this->handleFuelVolumeChangedCase($fuelFlow);
        }
    }

    public function handleFuelTankChangedCase()
    {
        $newFuelTankTransferHistory = FuelTankTransferHistory::create([
            'author_id' => Auth::id(),
            'fuel_tank_id' => $this->updatedData['fuel_tank_id'],
            'previous_object_id' => $this->fuelTankTransferHistory->previous_object_id,
            'object_id' => $this->fuelTankTransferHistory->object_id,
            'previous_responsible_id' => $this->fuelTankTransferHistory->previous_responsible_id,
            'responsible_id' => $this->fuelTankTransferHistory->responsible_id,
            'fuel_tank_flow_id' => $this->fuelTankTransferHistory->fuel_tank_flow_id,
            'fuel_level' => $this->getFuelLevel($this->fuelTankTransferHistory),
            'event_date' => $this->updatedData['event_date']
        ]);
        new FuelLevelSyncOnFlowCreatedService($newFuelTankTransferHistory);
        new FuelLevelSyncOnFlowDeletedService($this->fuelTankTransferHistory);
        $this->fuelTankTransferHistory->delete();

        $this->fuelTankTransferHistory = $newFuelTankTransferHistory;
    }

    public function handleFuelVolumeChangedCase()
    {
        $this->setFuelTankTransferHistoryData();
        $this->updateChainOfFuelTankTransferHistories();
        $this->updateFuelTankFuelLevel();
    }

    public function setFuelTankTransferHistoryData()
    {
        $this->fuelTankTransferHistory->fuel_level = $this->getFuelLevel($this->fuelTankTransferHistory);
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

    public function getFuelLevel($fuelTankTransferHistory)
    {
        $fuelTankFlow = FuelTankFlow::find($fuelTankTransferHistory->fuel_tank_flow_id);
        $currentFuelLevel = $fuelTankTransferHistory->fuel_level ?? 0;

        if(!$fuelTankFlow && !$this->fuelVolumeDiff) {
            return $currentFuelLevel;
        }

        if(FuelTankFlowType::find($fuelTankFlow->fuel_tank_flow_type_id)->slug === 'outcome') {
            return $currentFuelLevel - $this->fuelVolumeDiff;
        }

        if(FuelTankFlowType::find($fuelTankFlow->fuel_tank_flow_type_id)->slug === 'income') {
            return $currentFuelLevel + $this->fuelVolumeDiff;
        }

        if(FuelTankFlowType::find($fuelTankFlow->fuel_tank_flow_type_id)->slug === 'adjustment') {
            return $currentFuelLevel + $this->fuelVolumeDiff;
        }
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

    
}