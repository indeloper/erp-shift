<?php

namespace App\Jobs\WorkFlowSupport\Technic;

use App\Models\TechAcc\TechnicMovement;
use App\Notifications\Technic\TechnicMovementNotifications;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TechnicMovementDayBeforeReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $technicMovement = TechnicMovement::find($this->data['entity']->id); 
        $movementStartDatetimeLocal = Carbon::parse($this->data['updateData']['movement_start_datetime'])->setTimezone('Europe/Moscow');

        if($technicMovement->movement_start_datetime != $movementStartDatetimeLocal)
        return;
    
        (new TechnicMovementNotifications)
            ->notifyAboutTechnicMovementPlannedForTommorow(
                $this->data['updateData'], 
                $this->data['entity'], 
                $this->data['notificationRecipientsIds']
            );
    }
}
