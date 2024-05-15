<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use App\Models\User;
use App\Notifications\Fuel\FuelNotifications;
use App\Notifications\Fuel\FuelOfficeResponsiblesAboutTankMovingConfirmationDelayedNotification;
use App\Notifications\Fuel\NewFuelTankResponsibleNotification;
use Illuminate\Console\Command;

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
        foreach ($fuelTanksAwaitingMovingConfirmation as $tank) {

            NewFuelTankResponsibleNotification::send(
                $tank->responsible_id,
                [
                    'name' => (new FuelNotifications)->renderNewFuelTankResponsible($tank),
                    'tank_id' => $tank->id,
                ]
            );

            $lastTankTransferHistory = FuelTankTransferHistory::query()
                ->where('fuel_tank_id', $tank->id)
                ->whereNull('fuel_tank_flow_id')
                ->orderByDesc('id')
                ->first();

            $newResponsible = User::find($tank->responsible_id);

            $previousResponsible = User::find($lastTankTransferHistory->previous_responsible_id);

            foreach ($notificationRecipientsOffice as $userId) {

                FuelOfficeResponsiblesAboutTankMovingConfirmationDelayedNotification::send(
                    $userId,
                    [
                        'name' => 'Перемещение топливной емкости',
                        'tank' => $tank,
                        'lastTankTransferHistory' => $lastTankTransferHistory,
                        'newResponsible' => $newResponsible,
                        'previousResponsible' => $previousResponsible,
                    ]
                );
            }
        }
    }
}
