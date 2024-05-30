<?php

namespace App\Observers\Manual;

use App\Models\Manual\ManualNodeMaterials;

class ManualNodeMaterialsObserver
{
    /**
     * Handle the manual node materials "saving" event.
     */
    public function saving(ManualNodeMaterials $manualNodeMaterials): void
    {
        if (! $manualNodeMaterials->unit) {
            $manualNodeMaterials->unit = $manualNodeMaterials->materials->category->category_unit;
        }
    }
}
