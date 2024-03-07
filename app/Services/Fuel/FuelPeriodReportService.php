<?php

namespace App\Services\Fuel;

use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use Carbon\Carbon;

class FuelPeriodReportService {
    public function getSummaryDataFuelFlowPeriodReport($objectTransferGroups, $responsibleId, $fuelTankId, $objectId, $globalDateFrom, $globalDateTo)
    {
        if($this->checkNeedToSkip($objectTransferGroups)) {
            return null;
        }

        $reportData = $this->getReportData($objectTransferGroups, $responsibleId, $fuelTankId, $objectId, $globalDateFrom, $globalDateTo);

        return [
            'fuelLevelPeriodStart' => $reportData['fuelLevelPeriodStart'],
            'fuelLevelPeriodFinish' => $reportData['fuelLevelPeriodFinish'],
            'confirmedTankMovements' => $reportData['confirmedTankMovements'],
            'dateFrom' => $reportData['dateFrom']->format('d.m.Y'),
            'dateTo' => $reportData['dateTo']->format('d.m.Y'),
        ];
    }

    public function getReportData($objectTransferGroups, $responsibleId, $fuelTankId, $objectId, $globalDateFrom, $globalDateTo)
    {
        $reportData['dateFrom'] = $this->getDateFrom($objectTransferGroups, $responsibleId, $fuelTankId, $objectId, $globalDateFrom, $globalDateTo);
        $reportData['dateTo'] = $this->getDateTo($objectTransferGroups, $responsibleId, $fuelTankId, $objectId, $globalDateFrom, $globalDateTo);
        $reportData['fuelLevelPeriodStart'] = $this->getFuelLevelPeriodStart($objectTransferGroups, $reportData['dateFrom'], $reportData['dateTo'], $responsibleId, $fuelTankId, $objectId);
        $reportData['fuelLevelPeriodFinish'] = $this->getFuelLevelPeriodFinish($objectTransferGroups, $reportData['dateFrom'], $reportData['dateTo'], $responsibleId, $fuelTankId, $objectId);
        $reportData['confirmedTankMovements'] = $this->getConfirmedTankMovements($objectTransferGroups, $reportData['dateFrom'], $reportData['dateTo'], $responsibleId, $fuelTankId, $objectId);

        return $reportData;
    }

    public function getDateFrom($objectTransferGroups, $responsibleId, $fuelTankId, $objectId, $globalDateFrom, $globalDateTo)
    {
        $periodEventDates = $this->getPeriodEventDates($objectTransferGroups);
        if(empty($periodEventDates)) {
            return Carbon::create($globalDateFrom);
        }

        $minEventDate = min($periodEventDates);

        $isMinDateMovementEvent = $this->checkIsDateMovementEventFrom($minEventDate, $responsibleId, $fuelTankId, $objectId);

        if($isMinDateMovementEvent) {
            return max(
                Carbon::create($minEventDate),
                Carbon::create($globalDateFrom));
        }

        return Carbon::create($globalDateFrom);
    }

    public function getDateTo($objectTransferGroups, $responsibleId, $fuelTankId, $objectId, $globalDateFrom, $globalDateTo)
    {
        if(isset($objectTransferGroups['transitionPeriod'])) {
            $dateTo = $this->getTransitionPeriodDateTo($responsibleId, $fuelTankId, $objectId, $globalDateFrom, $globalDateTo);
            return Carbon::create($dateTo);
        }

        $periodEventDates = $this->getPeriodEventDates($objectTransferGroups);
        if(empty($periodEventDates)) {
            return Carbon::create($globalDateTo);
        }

        $maxEventDate = max($periodEventDates);

        $nextReportMovementConfirmationDate = $this->getNextReportMovementConfirmationDate($maxEventDate, $responsibleId, $fuelTankId, $objectId);

        if(!$nextReportMovementConfirmationDate) {
            return Carbon::create($globalDateTo);
        }

        return min(
            Carbon::create($nextReportMovementConfirmationDate),
            Carbon::create($globalDateTo));
    }

    public function getFuelLevelPeriodStart($objectTransferGroups, $dateFrom, $dateTo, $responsibleId, $fuelTankId, $objectId)
    {
        $isMinDateMovementEvent = $this->checkIsDateMovementEventFrom($dateFrom, $responsibleId, $fuelTankId, $objectId);

        if(
            isset($objectTransferGroups['transitionPeriod'])
            || isset($objectTransferGroups['notIncludedTank'])
            || !$isMinDateMovementEvent
        ) {
            return FuelTankTransferHistory::query()
                ->where([
                    ['event_date', '<', $dateFrom],
                    ['fuel_tank_id', $fuelTankId],
                ])
                ->orderByDesc('event_date')
                ->orderByDesc('id')
                ->first()
                ->fuel_level ?? 0;
        }

        return FuelTankTransferHistory::query()
            ->where([
                ['event_date', '<=', $dateFrom],
                ['responsible_id', '<>', $responsibleId],
                ['fuel_tank_id', $fuelTankId],
                // ['object_id', $objectId]
            ])
            ->orWhere([
                ['event_date', '<=', $dateFrom],
                // ['responsible_id', $responsibleId],
                ['fuel_tank_id', $fuelTankId],
                ['object_id', '<>', $objectId]
            ])
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->first()
            ->fuel_level ?? 0;
    }

    public function getFuelLevelPeriodFinish($objectTransferGroups, $dateFrom, $dateTo, $responsibleId, $fuelTankId, $objectId)
    {
        $periodEventDates = $this->getPeriodEventDates($objectTransferGroups);

        if(isset($objectTransferGroups['notIncludedTank']) || empty($periodEventDates)) {
            return $this->getFuelLevelPeriodStart($objectTransferGroups, $dateFrom, $dateTo, $responsibleId, $fuelTankId, $objectId);
        }

        return FuelTankTransferHistory::query()
            ->where([
                ['event_date', '>=', $dateFrom],
                ['event_date', '<=', $dateTo],
                ['responsible_id', $responsibleId],
                ['fuel_tank_id', $fuelTankId],
                ['object_id', $objectId]
            ])
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->first()
            ->fuel_level ?? 0;
    }

    public function getConfirmedTankMovements($objectTransferGroups, $dateFrom, $dateTo, $responsibleId, $fuelTankId, $objectId)
    {
        return FuelTankTransferHistory::query()
            ->where([
                ['fuel_tank_id', $fuelTankId],
                ['event_date', '>=',  $dateFrom],
                ['event_date', '<=',  $dateTo],
                ['responsible_id', $responsibleId],
                ['object_id', $objectId],
                ['tank_moving_confirmation', true]
            ])
            ->orWhere([
                ['fuel_tank_id', $fuelTankId],
                ['event_date', '>=',  $dateFrom],
                ['event_date', '<=',  $dateTo],
                ['previous_responsible_id', $responsibleId],
                ['previous_object_id', $objectId],
                ['tank_moving_confirmation', true]
            ])
            ->orWhere([
                ['fuel_tank_id', $fuelTankId],
                ['event_date', '>=',  $dateFrom],
                ['event_date', '<=',  $dateTo],
                ['previous_responsible_id', $responsibleId],
                ['object_id', $objectId],
                ['tank_moving_confirmation', true]
            ])
            ->orWhere([
                ['fuel_tank_id', $fuelTankId],
                ['event_date', '>=',  $dateFrom],
                ['event_date', '<=',  $dateTo],
                ['responsible_id', $responsibleId],
                ['previous_object_id', $objectId],
                ['tank_moving_confirmation', true]
            ])
            ->whereNotNull('object_id')
            ->whereNotNull('previous_object_id')
            ->orderBy('event_date')
            ->orderBy('id')
            ->get()
            ->toArray();
    }

    public function getPeriodEventDates($objectTransferGroups)
    {
        $eventDates = [];
        array_walk_recursive($objectTransferGroups, function($value, $key) use(&$eventDates) {
            if($key === 'event_date')
            $eventDates[] = $value;
        });

        return $eventDates;
    }

    public function checkIsDateMovementEventFrom($minEventDate, $responsibleId, $fuelTankId, $objectId)
    {
        return FuelTankTransferHistory::query()
            ->where([
                ['tank_moving_confirmation', true],
                ['event_date', $minEventDate],
                ['responsible_id', $responsibleId],
                ['fuel_tank_id', $fuelTankId],
                ['object_id', $objectId]
            ])
            ->exists();
    }

    public function getNextReportMovementConfirmationDate($maxEventDate, $responsibleId, $fuelTankId, $objectId)
    {
        return FuelTankTransferHistory::query()
            ->where([
                ['tank_moving_confirmation', true],
                ['event_date', '>=', $maxEventDate],
                ['previous_responsible_id', $responsibleId],
                ['fuel_tank_id', $fuelTankId],
                ['previous_object_id', $objectId]
            ])
            ->orderBy('event_date')
            ->first()
            ->event_date ?? null;
    }

    public function getTransitionPeriodDateTo($responsibleId, $fuelTankId, $objectId, $globalDateFrom, $globalDateTo)
    {
        return FuelTankTransferHistory::query()
        ->where([
            ['tank_moving_confirmation', true],
            ['event_date', '>=', Carbon::create($globalDateFrom)],
            ['previous_responsible_id', $responsibleId],
            ['fuel_tank_id', $fuelTankId],
            ['previous_object_id', $objectId]
        ])
        ->orderBy('event_date')
        ->first()
        ->event_date ?? $globalDateTo;
    }

    public function checkNeedToSkip($objectTransferGroups)
    {
        if(
            count($objectTransferGroups) === 1
            && isset($objectTransferGroups[0][0])
        ) {
            $a = (int)$objectTransferGroups[0][0]["fuel_tank_flow_type_slug"] ?? NULL;
            $b = (int)$objectTransferGroups[0][0]["tank_moving_confirmation"] ?? NULL;

            return $a + $b < 1;
        }
    }
}
