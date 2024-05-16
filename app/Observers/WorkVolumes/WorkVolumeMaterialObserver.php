<?php

namespace App\Observers\WorkVolumes;

use App\Models\WorkVolume\WorkVolumeMaterial;

class WorkVolumeMaterialObserver
{
    /**
     * Handle the work volume material "saving" event.
     *
     * @return void
     */
    public function saving(WorkVolumeMaterial $workVolumeMaterial)
    {
        if (! $workVolumeMaterial->material_type) {
            $workVolumeMaterial->material_type = 'regular';
        }

        if (! $workVolumeMaterial->unit) {
            $workVolumeMaterial->unit = $workVolumeMaterial->manual->category->category_unit;
        }
    }
}
