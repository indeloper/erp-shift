<?php

namespace App\Observers\WorkVolumes;

use App\Models\WorkVolume\WorkVolumeWork;

class WorkVolumeWorkObserver
{
    /**
     * Handle the work volume work "saving" event.
     */
    public function saving(WorkVolumeWork $workVolumeWork): void
    {
        if (! $workVolumeWork->unit) {
            $workVolumeWork->unit = $workVolumeWork->manual->unit;
        }
    }
}
