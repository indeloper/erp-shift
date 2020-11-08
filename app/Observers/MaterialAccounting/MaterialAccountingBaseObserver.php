<?php

namespace App\Observers\MaterialAccounting;

use App\Models\MatAcc\MaterialAccountingBase;
use App\Models\Manual\ManualMaterial;

class MaterialAccountingBaseObserver
{
    /**
     * Handle the material accounting base "saving" event.
     *
     * @param  MaterialAccountingBase  $materialAccountingBase
     * @return void
     */
    public function saving(MaterialAccountingBase $materialAccountingBase)
    {
        if (!$materialAccountingBase->unit) {
            $materialAccountingBase->unit = $materialAccountingBase->material->category_unit;
        }
    }
}
