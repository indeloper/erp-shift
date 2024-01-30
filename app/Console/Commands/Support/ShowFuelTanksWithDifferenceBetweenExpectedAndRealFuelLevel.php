<?php

namespace App\Console\Commands\Support;

use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use Illuminate\Console\Command;

class ShowFuelTanksWithDifferenceBetweenExpectedAndRealFuelLevel extends Command
{
   /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'show:fuelTanksWithDifferenceBetweenExpectedAndRealFuelLevel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shows Fuel Tanks With Difference Between Expected AndReal Fuel Level';

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
        $fuelTanks = FuelTank::all();
        foreach($fuelTanks as $fuelTank) {
            
            if($fuelTank->fuel_level - $this->getFuelLevel($fuelTank->id)) {
                echo PHP_EOL.PHP_EOL;
                echo PHP_EOL.$fuelTank->tank_number.' / '.$fuelTank->id.' - номер / id емкости ';
                echo PHP_EOL.'tank_fuel_level: '.$fuelTank->fuel_level;
                echo PHP_EOL.'fuel_level_should_be: '.$this->getFuelLevel($fuelTank->id);
            }
            
        }
    }

    public function getFuelLevel($fuelTankId)
    {
        return FuelTankTransferHistory::where('fuel_tank_id', $fuelTankId)
            ->orderByDesc('event_date')
            ->orderByDesc('parent_fuel_level_id')
            ->first()
            ->fuel_level;
    }
}
