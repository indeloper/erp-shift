<?php

namespace App\Observers\WorkVolumes;

use App\Models\WorkVolume\WorkVolumeWork;
use App\Models\Manual\ManualWork;

class WorkVolumeWorkObserver
{
    /**
     * Handle the work volume work "saving" event.
     *
     * @param  WorkVolumeWork  $workVolumeWork
     * @return void
     */
    public function saving(WorkVolumeWork $workVolumeWork)
    {
        if (!$workVolumeWork->unit) {
            $workVolumeWork->unit = $workVolumeWork->manual->unit;
        }
    }
}
