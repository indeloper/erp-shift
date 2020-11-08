<?php

namespace App\Observers\Manual;

use App\Models\Manual\ManualNodeMaterials;
use App\Models\Manual\ManualMaterial;

class ManualNodeMaterialsObserver
{
    /**
     * Handle the manual node materials "saving" event.
     *
     * @param  ManualNodeMaterials  $manualNodeMaterials
     * @return void
     */
    public function saving(ManualNodeMaterials $manualNodeMaterials)
    {
        if (!$manualNodeMaterials->unit) {
            $manualNodeMaterials->unit = $manualNodeMaterials->materials->category->category_unit;
        }
    }
}
