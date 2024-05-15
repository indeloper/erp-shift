<?php

namespace App\Console\Commands\Support\Fuel;

use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankFlow;
use App\Models\TechAcc\FuelTank\FuelTankFlowType;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FuelTransferHistoriesSetFuelLevels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:fuelTransferHistoriesFuelLevels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузка остатков топлива в таблицу fuel_tank_transfer_histories';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        $choosedId = (int) $this->ask('Укажите id топливной емкости или оставьте пустым для обновления по всем емкостям');
        $choosedEventDate = $this->ask('Укажите дату с которой будет выполнен пересчет остатков или оставьте пустым для обновления по всем операциям');

        if ($choosedId) {
            $fuelTanksIds[] = $choosedId;
        } else {
            $fuelTanksIds = FuelTank::pluck('id');
        }

        foreach ($fuelTanksIds as $fuelTankId) {
            $previousTransferHistory = $this->getPreviousTransferHistory($fuelTankId, $choosedEventDate);
            $iteratableTransferHistories = $this->getIteratableTransferHistories($previousTransferHistory, $fuelTankId);
            $fuelLevel = $previousTransferHistory->fuel_level ?? 0;

            foreach ($iteratableTransferHistories as $history) {
                $history->fuel_level = $this->getFuelLevel($fuelLevel, $history);
                $history->save();
                $fuelLevel = $history->fuel_level;
            }

            $tank = FuelTank::find($fuelTankId);
            $tank->fuel_level = $fuelLevel;
            $tank->save();
        }
    }

    public function getPreviousTransferHistory($fuelTankId, $choosedEventDate)
    {
        return FuelTankTransferHistory::query()
            ->where([
                ['fuel_tank_id', $fuelTankId],
                ['event_date', '<', Carbon::create($choosedEventDate)],
            ])
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->first();
    }

    public function getIteratableTransferHistories($previousTransferHistory, $fuelTankId)
    {
        if (! $previousTransferHistory) {
            return FuelTankTransferHistory::query()
                ->where([
                    ['fuel_tank_id', $fuelTankId],
                ])
                ->orderBy('event_date')
                ->orderBy('id')
                ->get();
        }

        return FuelTankTransferHistory::query()
            ->where([
                ['fuel_tank_id', $fuelTankId],
                ['event_date', $previousTransferHistory->event_date],
                ['id', '>', $previousTransferHistory->id],
            ])
            ->orWhere([
                ['fuel_tank_id', $fuelTankId],
                ['event_date', '>', $previousTransferHistory->event_date],
            ])
            ->orderBy('event_date')
            ->orderBy('id')
            ->get();
    }

    public function getFuelLevel($fuelLevelBefore, $history)
    {
        if (! $history->fuel_tank_flow_id) {
            return $fuelLevelBefore;
        }

        $fuelFlow = FuelTankFlow::find($history->fuel_tank_flow_id);

        if (FuelTankFlowType::find($fuelFlow->fuel_tank_flow_type_id)->slug === 'outcome') {
            return $fuelLevelBefore + (-1 * $fuelFlow->volume);
        }

        return $fuelFlow->volume + $fuelLevelBefore;
    }
}
