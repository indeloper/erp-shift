<?php

namespace App\Console\Commands\Support;

use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use App\Models\User;
use App\Services\Fuel\FuelLevelSyncOnFlowCreatedService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class FuelTransferHistoriesUploadData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:fuelTransferHistories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload fuel_tank_transfer_histories table data';

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
    public function handle()
    {
        $choosedId = (int)$this->ask('Укажите id топливной емкости или оставьте пустым для обновления по всем емкостям');
        $choosedEventDate = $this->ask('Укажите дату с которой будет выполнен пересчет остатков или оставьте пустым для обновления по всем операциям');

        if($choosedId) {
            $fuelTanksIds[] = $choosedId;
        } else {
            $fuelTanksIds = FuelTank::pluck('id');
        }

        foreach($fuelTanksIds as $fuelTanksId) {

            $newFuelTankTransferHistory = $this->getNewFuelTransferHistory($fuelTanksId, $choosedEventDate);

            new FuelLevelSyncOnFlowCreatedService($newFuelTankTransferHistory);

            $newFuelTankTransferHistoryChild = FuelTankTransferHistory::where('parent_fuel_level_id', $newFuelTankTransferHistory->id)->first();
            if($newFuelTankTransferHistoryChild) {
                $newFuelTankTransferHistoryChild->parent_fuel_level_id = $newFuelTankTransferHistory->parent_fuel_level_id;
                $newFuelTankTransferHistoryChild->save();
            }
            $newFuelTankTransferHistory->delete();
        }
    }

    public function getNewFuelTransferHistory($fuelTanksId, $choosedEventDate)
    {
        if($choosedEventDate) {
            $parentTransferHistory = FuelTankTransferHistory::whereNotNull('fuel_tank_flow_id')
                ->where([
                    ['fuel_tank_id', $fuelTanksId],
                    ['event_date', '<', Carbon::create($choosedEventDate)],
                ])
            ->orderByDesc('event_date')
            ->orderByDesc('parent_fuel_level_id')
            ->first();

            return FuelTankTransferHistory::create([
                'author_id' => User::where('user_full_name', 'Тихонов С. А.')->first()->id,
                'fuel_tank_id' => $fuelTanksId,
                'fuel_level' => $parentTransferHistory->fuel_level ?? 0,
                'event_date' =>  Carbon::create($choosedEventDate)->subDay()
            ]);
        } else {
            return FuelTankTransferHistory::create([
                'author_id' => User::where('user_full_name', 'Тихонов С. А.')->first()->id,
                'fuel_tank_id' => $fuelTanksId,
                'fuel_level' => 0,
                'event_date' => Carbon::create(FuelTankTransferHistory::min('event_date'))->subDay()
            ]);
        }
    }
}
