<?php

namespace App\Services\Fuel;

use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankFlowType;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FuelFlowCrudService
{
    protected $methodName;

    protected $serviceData;

    public function __construct($methodName, $serviceData)
    {
        $this->methodName = $methodName;
        $this->serviceData = $serviceData;
        $this->handle();
    }

    public function handle()
    {
        if ($this->methodName === 'stored') {
            $this->stored();
        }
        if ($this->methodName === 'deleted') {
            $this->deleted();
        }
        if ($this->methodName === 'updated') {
            $this->updated();
        }
    }

    public function stored()
    {
        $previousTransferHistory = $this->getPreviousTransferHistory();
        $fuelLevelDiff = $this->getFuelLevelDiff('stored');
        $this->createCorrespondingTransferHistory($previousTransferHistory, $fuelLevelDiff);
        $subsequentTransferHistories = $this->getSubsequentTransferHistories('stored');
        $this->updateSubsequentTransferHistories($subsequentTransferHistories, $fuelLevelDiff);
        $this->updateFuelTank($fuelLevelDiff);
    }

    public function deleted()
    {
        $fuelLevelDiff = (-1 * $this->getFuelLevelDiff('deleted'));
        $subsequentTransferHistories = $this->getSubsequentTransferHistories('deleted');
        $this->updateSubsequentTransferHistories($subsequentTransferHistories, $fuelLevelDiff);
        $this->updateFuelTank($fuelLevelDiff);
        FuelTankTransferHistory::where('fuel_tank_flow_id', $this->serviceData['entity']->id)->delete();
    }

    public function updated()
    {
        $this->deleted();
        $this->stored();
    }

    public function getPreviousTransferHistory()
    {
        return FuelTankTransferHistory::query()
            ->where([
                ['fuel_tank_id', $this->serviceData['entity']->fuel_tank_id],
                ['event_date', '<=', Carbon::create($this->serviceData['data']['event_date'])],
            ])
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->first();
    }

    public function getFuelLevelDiff($callingMethod)
    {
        $volume =
            $callingMethod === 'stored' ?
                $this->serviceData['data']['volume']
                : $this->serviceData['entity']->volume;

        if (FuelTankFlowType::find($this->serviceData['entity']->fuel_tank_flow_type_id)->slug === 'outcome') {
            return -1 * $volume;
        }

        return $volume;
    }

    public function createCorrespondingTransferHistory($previousTransferHistory, $fuelLevelDiff)
    {
        return FuelTankTransferHistory::create([
            'author_id' => Auth::id(),
            'fuel_tank_id' => $this->serviceData['entity']->fuel_tank_id,
            'previous_object_id' => $previousTransferHistory->previous_object_id ?? $this->serviceData['entity']->object_id,
            'object_id' => $this->serviceData['entity']->object_id,
            'previous_responsible_id' => $previousTransferHistory->previous_responsible_id ?? $this->serviceData['entity']->responsible_id,
            'responsible_id' => $this->serviceData['entity']->responsible_id,
            'fuel_tank_flow_id' => $this->serviceData['entity']->id,
            'fuel_level' => $previousTransferHistory ? ($previousTransferHistory->fuel_level + $fuelLevelDiff) : 0,
            'event_date' => $this->serviceData['data']['event_date'],
        ]);
    }

    public function getSubsequentTransferHistories($callingMethod)
    {
        if ($callingMethod === 'stored') {
            return FuelTankTransferHistory::query()
                ->where([
                    ['fuel_tank_id', $this->serviceData['entity']->fuel_tank_id],
                    ['event_date', '>', $this->serviceData['data']['event_date']],
                ])
                ->get();
        }

        if ($callingMethod === 'deleted') {
            return FuelTankTransferHistory::query()
                ->where([
                    ['fuel_tank_id', $this->serviceData['entity']->fuel_tank_id],
                    ['event_date', $this->serviceData['entity']->event_date],
                    ['fuel_tank_flow_id', '>', $this->serviceData['entity']->id],
                ])
                ->orWhere([
                    ['fuel_tank_id', $this->serviceData['entity']->fuel_tank_id],
                    ['event_date', '>', $this->serviceData['entity']->event_date],
                ])
                ->get();
        }
    }

    public function updateSubsequentTransferHistories($subsequentTransferHistories, $fuelLevelDiff)
    {
        $subsequentTransferHistories->each(function ($history) use ($fuelLevelDiff) {
            $history->increment('fuel_level', $fuelLevelDiff);
        });
    }

    public function updateFuelTank($fuelLevelDiff)
    {
        $tank = FuelTank::find($this->serviceData['entity']->fuel_tank_id)->increment('fuel_level', $fuelLevelDiff);
    }
}
