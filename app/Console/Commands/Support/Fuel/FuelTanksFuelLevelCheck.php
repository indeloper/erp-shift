<?php

namespace App\Console\Commands\Support\Fuel;

use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankFlow;
use App\Models\TechAcc\FuelTank\FuelTankFlowType;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use App\Notifications\Fuel\FuelNotifications;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class FuelTanksFuelLevelCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:fuelTanksFuelLevel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if fact and expected fuel levels are the same';

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
    public function configure()
    {
        $this->addOption('dateFrom', null, InputOption::VALUE_REQUIRED, 'Начальная дата, с которой ведется расчет');
    }

    public function handle(): void
    {
        $dateFrom = Carbon::parse($this->option('dateFrom'));
        foreach (FuelTank::all() as $tank) {
            $transferHistoryFuelLevelDateFrom = $this->getTransferHistoryFuelLevelDateFrom($tank, $dateFrom);
            $fuelOperationsVolumesSum = $this->getFuelOperationsVolumesSum($tank, $dateFrom);
            $calculatedTankFuelLevel = (int) $transferHistoryFuelLevelDateFrom + (int) $fuelOperationsVolumesSum;
            $periodReportTankFuelLevel = $this->getPeriodReportTankFuelLevel($tank);
            $tankFuelLevel = $tank->fuel_level;
            $isOk =
                $calculatedTankFuelLevel === $periodReportTankFuelLevel
                && $calculatedTankFuelLevel === $tankFuelLevel
                ? true : false;

            if (! $isOk) {
                $data = [
                    'tank' => $tank,
                    'dateFrom' => $dateFrom,
                    'calculatedTankFuelLevel' => $calculatedTankFuelLevel,
                    'periodReportTankFuelLevel' => $periodReportTankFuelLevel,
                ];

                (new FuelNotifications)->notifyAdminsAboutFuelBalanceMissmatches($data);

                // echo PHP_EOL."
                //         id: $tank->id
                //         num: $tank->tank_number
                //         calculatedTankFuelLevel: $calculatedTankFuelLevel
                //         periodReportTankFuelLevel: $periodReportTankFuelLevel
                //         tankFuelLevel: $tankFuelLevel
                //         isOk: $isOk
                //         transferHistoryFuelLevelDateFrom: $transferHistoryFuelLevelDateFrom
                //         fuelOperationsVolumesSum: $fuelOperationsVolumesSum
                //     ";
            }
        }
    }

    public function getTransferHistoryFuelLevelDateFrom($tank, $dateFrom)
    {
        return FuelTankTransferHistory::where([
            ['fuel_tank_id', $tank->id],
            ['event_date', '<', $dateFrom],
            ['fuel_tank_flow_id', '<>', null],
        ])
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->first()
        ->fuel_level ?? 0;
    }

    public function getFuelOperationsVolumesSum($tank, $dateFrom)
    {
        $incomesAndAdjusmentsSum = FuelTankFlow::where([
            ['fuel_tank_id', $tank->id],
            ['event_date', '>=', $dateFrom],
        ])
            ->whereIn('fuel_tank_flow_type_id', FuelTankFlowType::whereIn('slug', ['income', 'adjustment'])->pluck('id')->toArray())
            ->sum('volume');

        $outcomesSum = FuelTankFlow::where([
            ['fuel_tank_id', $tank->id],
            ['event_date', '>=', $dateFrom],
        ])
            ->whereIn('fuel_tank_flow_type_id', FuelTankFlowType::whereIn('slug', ['outcome'])->pluck('id')->toArray())
            ->sum('volume');

        return $incomesAndAdjusmentsSum - $outcomesSum;
    }

    public function getPeriodReportTankFuelLevel($tank)
    {
        return FuelTankTransferHistory::where([
            ['fuel_tank_id', $tank->id],
            ['fuel_tank_flow_id', '<>', null],
        ])
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->first()
            ->fuel_level ?? 0;
    }
}
