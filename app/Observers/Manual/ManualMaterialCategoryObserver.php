<?php

namespace App\Observers\Manual;

use App\Models\Manual\ManualMaterialCategory;

class ManualMaterialCategoryObserver
{
    /**
     * Handle the manual material category "saved" event.
     *
     * @return void
     */
    public function saved(ManualMaterialCategory $manualMaterialCategory)
    {
        if ($manualMaterialCategory->isDirty('formula')) {
            $manualMaterialCategory->materials->each->makeMaterialName();
        }
    }
}
