<?php

namespace App\Services\Fuel;

use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankFlow;
use App\Models\TechAcc\FuelTank\FuelTankFlowType;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FuelLevelSyncOnFlowUpdatedService
{
    protected $fuelTankTransferHistory;
    protected $updatedData;
    protected $finalFuelLevelAmount;
    protected $fuelFlowVolumeDiff;

    public function __construct($fuelTankTransferHistory, $updatedData)
    {
        $this->fuelTankTransferHistory = $fuelTankTransferHistory;
        $this->updatedData = $updatedData;
        $this->fuelFlowVolumeDiff = $this->getFuelFlowVolumeDiff();

        $this->handle();
    }

    public function handle()
    {
        $fuelFlow = FuelTankFlow::find($this->updatedData['id']);
        
        $mode = $fuelFlow->event_date === $this->updatedData['event_date'] ? 
            'iteratingExistingChain' : 'creatingNewChain';
        $parentTransferHistory = $this->getParentTransferHistory($fuelFlow, $mode);
        if($mode === 'iteratingExistingChain') {
            $this->updateChainOfFuelTankTransferHistoriesByIteratingExistingChain($parentTransferHistory);
        }
        if($mode === 'creatingNewChain') {
            if($fuelFlow->event_date > $this->updatedData['event_date']) {
                $this->moveBackTransferHistory($parentTransferHistory);
            } else if($parentTransferHistory) {
                $this->moveForwardTransferHistory($parentTransferHistory);
            }
            
        }

        $this->updateFuelTankFuelLevel();
    }

    public function updateChainOfFuelTankTransferHistoriesByIteratingExistingChain($parentTransferHistory)
    {
        $currentFuelTankTransferHistory = true;

        $i=0;
        while($currentFuelTankTransferHistory) {
            $currentFuelTankTransferHistory = FuelTankTransferHistory::where('parent_fuel_level_id', $parentTransferHistory->id)->first();
            if($currentFuelTankTransferHistory) {
                if($i===0) {
                    $parentTransferHistory->fuel_level += $this->fuelFlowVolumeDiff;
                } 

                $currentFuelTankTransferHistory->fuel_level = $this->getFuelLevel($currentFuelTankTransferHistory, $parentTransferHistory->fuel_level);
                
                $currentFuelTankTransferHistory->save();
                $parentTransferHistory = $currentFuelTankTransferHistory;
                $this->finalFuelLevelAmount = $currentFuelTankTransferHistory->fuel_level;
            }
            $i++;
        }
    }

    public function moveBackTransferHistory($parentTransferHistory)
    {
        $fuelTankTransferHistoriesToUpdate = $this->getFuelTankTransferHistoriesToUpdateBackMovement();
        $this->setFuelTankTransferHistoryData($this->fuelTankTransferHistory, $parentTransferHistory, $updateEventDate = true);
        // $this->setFuelTankTransferHistoryData($parentTransferHistory);
        $this->fuelTankTransferHistory->fuel_level += $this->fuelFlowVolumeDiff;
        $this->fuelTankTransferHistory->save();
        
        $parentFuelLevelId = $this->fuelTankTransferHistory->id;
        $fuelLevel = $this->fuelTankTransferHistory->fuel_level;

        foreach($fuelTankTransferHistoriesToUpdate as $transferHistory) {
            $transferHistory->parent_fuel_level_id = $parentFuelLevelId;
            $transferHistory->fuel_level = $this->getFuelLevel($transferHistory, $fuelLevel);
            $transferHistory->save();

            $parentFuelLevelId = $transferHistory->id;
            $fuelLevel = $transferHistory->fuel_level;
        }

        $this->finalFuelLevelAmount = $fuelLevel;
    }

    public function moveForwardTransferHistory($newParentTransferHistory)
    {
        $childTransferHistory = FuelTankTransferHistory::where('parent_fuel_level_id', $this->fuelTankTransferHistory->id)->first();
        
        if(!$childTransferHistory || Carbon::create($childTransferHistory->event_date) > Carbon::create($this->updatedData['event_date'])) {
            $this->fuelTankTransferHistory->fuel_level += $this->fuelFlowVolumeDiff;
            $this->fuelTankTransferHistory->save();
            return;
        }

        $parentTransferHistory = FuelTankTransferHistory::find($this->fuelTankTransferHistory->parent_fuel_level_id);
        $childTransferHistory = $this->setFuelTankTransferHistoryData($childTransferHistory, $parentTransferHistory, $updateEventDate = false);
        $childTransferHistory->save();

        $newChildTransferHistory = FuelTankTransferHistory::where('parent_fuel_level_id', $newParentTransferHistory->id ?? 0)->first();
        
        $this->fuelTankTransferHistory->event_date = $this->updatedData['event_date'];
        $this->fuelTankTransferHistory->parent_fuel_level_id = $newParentTransferHistory->id;
        $this->fuelTankTransferHistory->save();
       
        if($newChildTransferHistory) {
            $newChildTransferHistory->parent_fuel_level_id = $this->fuelTankTransferHistory->id;
            $newChildTransferHistory->save();
        }
        
        $this->updateChainOfFuelTankTransferHistoriesByIteratingExistingChain($parentTransferHistory);
    }

    public function setFuelTankTransferHistoryData($transferHistory, $parentTransferHistory, $updateEventDate = false)
    {                
        if($parentTransferHistory) {
            $transferHistory->parent_fuel_level_id = $parentTransferHistory->id;
            $transferHistory->fuel_level = $this->getFuelLevel($transferHistory, $parentTransferHistory->fuel_level);
        } else {
            $transferHistory->parent_fuel_level_id = null;
            $transferHistory->fuel_level = 0;
        }
        if($updateEventDate) {
            $transferHistory->event_date = $this->updatedData['event_date'];
        }
       
        $transferHistory->save();

        return $transferHistory;
    }


    // public function setFuelTankTransferHistoryData($parentTransferHistory)
    // {                
    //     if($parentTransferHistory) {
    //         $this->fuelTankTransferHistory->parent_fuel_level_id = $parentTransferHistory->id;
    //         $this->fuelTankTransferHistory->fuel_level = $this->getFuelLevel($this->fuelTankTransferHistory, $parentTransferHistory->fuel_level);
    //     } else {
    //         $this->fuelTankTransferHistory->parent_fuel_level_id = null;
    //         $this->fuelTankTransferHistory->fuel_level = 0;
    //     }
        
    //     $this->fuelTankTransferHistory->event_date = $this->updatedData['event_date'];
    //     $this->fuelTankTransferHistory->save();
    // }

    public function getFuelTankTransferHistoriesToUpdateBackMovement()
    {
        $minEventDate = min($this->fuelTankTransferHistory->event_date, $this->updatedData['event_date']);
        return FuelTankTransferHistory::where([
            ['fuel_tank_id',  $this->fuelTankTransferHistory->fuel_tank_id],
            ['event_date', '>', $minEventDate],
            // ['event_date', '>', $this->updatedData['event_date']],
            ['fuel_tank_flow_id', '<>', $this->fuelTankTransferHistory->fuel_tank_flow_id]
        ])
        ->orderBy('event_date')
        ->orderBy('id')
        ->get();
    } 
    
    public function getFuelTankTransferHistoriesToUpdateFowardMovement($childTransferHistory)
    {

    }

    public function getFuelFlowVolumeDiff()
    {
        $fuelTankFlow = FuelTankFlow::find($this->updatedData['id']);
        $flowType = FuelTankFlowType::find($this->updatedData['fuel_tank_flow_type_id'])->slug;

        if($flowType === 'outcome') {
            return  -1 * round($this->updatedData['volume'] - $fuelTankFlow->volume);
        }

        return round($this->updatedData['volume'] - $fuelTankFlow->volume);
    }

    public function getParentTransferHistory($fuelFlow, $mode)
    {
        if($mode === 'creatingNewChain') {
            return FuelTankTransferHistory::where([
                ['fuel_tank_id',  $this->fuelTankTransferHistory->fuel_tank_id],
                ['fuel_tank_flow_id', '<>', NULL],
                ['event_date', '<=', $this->updatedData['event_date']],
                ['fuel_tank_flow_id', '<>', $this->fuelTankTransferHistory->fuel_tank_flow_id]
            ])
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->first();
        } 

        $fuelFlowTransferHistory = FuelTankTransferHistory::where([
            ['fuel_tank_flow_id', $fuelFlow->id],
        ])
        ->first();

        if(!$fuelFlowTransferHistory->parent_fuel_level_id) {
            return null;
        }

        return FuelTankTransferHistory::find($fuelFlowTransferHistory->parent_fuel_level_id);

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

        $flowType = FuelTankFlowType::find($fuelTankFlow->fuel_tank_flow_type_id)->slug;

        if($flowType === 'outcome') {
            return round($currentFuelLevel - $fuelTankFlow->volume);
        }

        if($flowType === 'income') {
            return round($currentFuelLevel + $fuelTankFlow->volume);
        }

        if($flowType === 'adjustment') {
            return round($currentFuelLevel + $fuelTankFlow->volume);
        }
        
    }    
}