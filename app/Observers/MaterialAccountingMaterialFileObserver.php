<?php

namespace App\Observers;

use App\Models\MatAcc\MaterialAccountingMaterialFile;

class MaterialAccountingMaterialFileObserver
{
    /**
     * Handle the material accounting material file "created" event.
     *
     * @param  MaterialAccountingMaterialFile  $materialAccountingMaterialFile
     * @return void
     */
    public function created(MaterialAccountingMaterialFile $materialAccountingMaterialFile)
    {
        //
    }

    /**
     * Handle the material accounting material file "created" event.
     *
     * @param  MaterialAccountingMaterialFile  $materialAccountingMaterialFile
     * @return void
     */
    public function saved(MaterialAccountingMaterialFile $materialAccountingMaterialFile)
    {
        $operationMaterial = $materialAccountingMaterialFile->operationMaterial;
        if ($operationMaterial) {
            $operation = $operationMaterial->operation;
            if (in_array($operation->type, [1, 4]) && ! ($operation->materialsPartTo()->doesntHave('certificates')->exists())) {
                $task = $operation->tasksMorphed()->where('status', 43)->where('is_solved', 0)->first();
                if ($task) {
                    $task->solve_n_notify();
                }
            }
        }
    }

    /**
     * Handle the material accounting material file "updated" event.
     *
     * @param  MaterialAccountingMaterialFile  $materialAccountingMaterialFile
     * @return void
     */
    public function updated(MaterialAccountingMaterialFile $materialAccountingMaterialFile)
    {
        //
    }

    /**
     * Handle the material accounting material file "deleted" event.
     *
     * @param  MaterialAccountingMaterialFile  $materialAccountingMaterialFile
     * @return void
     */
    public function deleted(MaterialAccountingMaterialFile $materialAccountingMaterialFile)
    {
        //
    }

    /**
     * Handle the material accounting material file "restored" event.
     *
     * @param  MaterialAccountingMaterialFile  $materialAccountingMaterialFile
     * @return void
     */
    public function restored(MaterialAccountingMaterialFile $materialAccountingMaterialFile)
    {
        //
    }

    /**
     * Handle the material accounting material file "force deleted" event.
     *
     * @param  MaterialAccountingMaterialFile  $materialAccountingMaterialFile
     * @return void
     */
    public function forceDeleted(MaterialAccountingMaterialFile $materialAccountingMaterialFile)
    {
        //
    }
}
