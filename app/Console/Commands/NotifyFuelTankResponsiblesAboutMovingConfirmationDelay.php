<?php

namespace App\Console\Commands;

use App\Domain\DTO\NotificationData;
use App\Domain\Enum\NotificationType;
use App\Jobs\Notification\NotificationJob;
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

            NotificationJob::dispatchNow(
                new NotificationData(
                    $tank->responsible_id,
                    (new FuelNotifications)->renderNewFuelTankResponsible($tank),
                    'Перемещение топливной емкости',
                    NotificationType::FUEL_NEW_TANK_RESPONSIBLE,
                    [
                        'tank_id' => $tank->id
                    ]
                )
            );

            $lastTankTransferHistory = FuelTankTransferHistory::query()
                ->where('fuel_tank_id', $tank->id)
                ->whereNull('fuel_tank_flow_id')
                ->orderByDesc('id')
                ->first();

            $newResponsible = User::find($tank->responsible_id);

            $previousResponsible = User::find($lastTankTransferHistory->previous_responsible_id);

            foreach ($notificationRecipientsOffice as $userId) {

                NotificationJob::dispatchNow(
                    new NotificationData(
                        $userId,
                        'Перемещение топливной емкости',
                        'Перемещение топливной емкости',
                        NotificationType::FUEL_NOTIFY_OFFICE_RESPONSIBLES_ABOUT_TANK_MOVING_CONFIRMATION_DELAYED,
                        [
                            'tank' => $tank,
                            'lastTankTransferHistory' => $lastTankTransferHistory,
                            'newResponsible' => $newResponsible,
                            'previousResponsible' => $previousResponsible
                        ]
                    )
                );
            }
        }
    }

    
}
