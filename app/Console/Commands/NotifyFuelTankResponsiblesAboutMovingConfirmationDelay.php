<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use App\Models\User;
use App\Notifications\Fuel\FuelNotifications;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotifyFuelTankResponsiblesAboutMovingConfirmationDelay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fuelTank:notifyAboutMovingConfirmationDelay';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notification to responsible users that moving confirmation is delayed';

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
        $fuelTanksAwaitingMovingConfirmation = FuelTank::where('awaiting_confirmation', 1)->get();
        $notificationRecipientsOffice = (new Permission)->getUsersIdsByCodename('notify_about_all_fuel_tanks_transfer');
        foreach($fuelTanksAwaitingMovingConfirmation as $tank) {
            (new FuelNotifications)->notifyNewFuelTankResponsibleUser($tank);

            foreach ($notificationRecipientsOffice as $userId) { 
                (new FuelNotifications)->notifyOfficeResponsiblesAboutFuelTankMovingConfirmationDelayed($tank, $userId);
            }
        }
    }

    
}
