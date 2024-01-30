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
        if($choosedId) {
            $fuelTanksIds[] = $choosedId;
        } else {
            $fuelTanksIds = FuelTank::pluck('id');
        }
        
        foreach($fuelTanksIds as $fuelTanksId) {
            $newFuelTankTransferHistory = FuelTankTransferHistory::create([
                'author_id' => User::where('user_full_name', 'Тихонов С. А.')->first()->id,
                'fuel_tank_id' => $fuelTanksId,
                'fuel_level' => 0,
                'event_date' => Carbon::create(FuelTankTransferHistory::min('event_date'))->subDay()
            ]);

            new FuelLevelSyncOnFlowCreatedService($newFuelTankTransferHistory);
        }
    }
}
