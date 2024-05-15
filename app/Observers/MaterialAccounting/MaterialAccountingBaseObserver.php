<?php

namespace App\Observers\MaterialAccounting;

use App\Models\MatAcc\MaterialAccountingBase;

class MaterialAccountingBaseObserver
{
    /**
     * Handle the material accounting base "saving" event.
     *
     * @return void
     */
    public function saving(MaterialAccountingBase $materialAccountingBase)
    {
        if (! $materialAccountingBase->unit) {
            $materialAccountingBase->unit = $materialAccountingBase->material->category_unit;
        }
    }

    public function created(MaterialAccountingBase $base): void
    {
        if ($base->ancestor_base_id == null) {
            $base->ancestor_base_id = $base->id;
            $base->save();
        }
    }
}
