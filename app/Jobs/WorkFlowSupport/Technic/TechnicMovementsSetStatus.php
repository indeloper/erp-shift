<?php

namespace App\Jobs\WorkFlowSupport\Technic;

use App\Models\TechAcc\TechnicMovement;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TechnicMovementsSetStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $technicMovementId;

    protected $newStatusId;

    protected $movementStartDatetime;

    public function __construct($technicMovementId, $newStatusId, $movementStartDatetime)
    {
        $this->technicMovementId = $technicMovementId;
        $this->newStatusId = $newStatusId;
        $this->movementStartDatetime = $movementStartDatetime;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $technicMovement = TechnicMovement::find($this->technicMovementId);
        $movementStartDatetimeLocal = Carbon::parse($this->movementStartDatetime)->setTimezone('Europe/Moscow');

        //сравниваем текщее значение movement_start_datetime и того, которое было на момент создания задачи
        if ($technicMovement->movement_start_datetime == $movementStartDatetimeLocal) {
            $technicMovement->update(['technic_movement_status_id' => $this->newStatusId]);
        }
    }
}
