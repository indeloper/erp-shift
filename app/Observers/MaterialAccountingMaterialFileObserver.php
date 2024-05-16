<?php

namespace App\Observers;

use App\Models\MatAcc\MaterialAccountingMaterialFile;

class MaterialAccountingMaterialFileObserver
{
    /**
     * Handle the material accounting material file "created" event.
     */
    public function created(MaterialAccountingMaterialFile $materialAccountingMaterialFile): void
    {
        //
    }

    /**
     * Handle the material accounting material file "created" event.
     */
    public function saved(MaterialAccountingMaterialFile $materialAccountingMaterialFile): void
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
     */
    public function updated(MaterialAccountingMaterialFile $materialAccountingMaterialFile): void
    {
        //
    }

    /**
     * Handle the material accounting material file "deleted" event.
     */
    public function deleted(MaterialAccountingMaterialFile $materialAccountingMaterialFile): void
    {
        //
    }

    /**
     * Handle the material accounting material file "restored" event.
     */
    public function restored(MaterialAccountingMaterialFile $materialAccountingMaterialFile): void
    {
        //
    }

    /**
     * Handle the material accounting material file "force deleted" event.
     */
    public function forceDeleted(MaterialAccountingMaterialFile $materialAccountingMaterialFile): void
    {
        //
    }
}
