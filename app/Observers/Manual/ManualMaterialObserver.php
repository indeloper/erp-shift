<?php

namespace App\Observers\Manual;

use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualReference;
use Illuminate\Support\Facades\DB;

class ManualMaterialObserver
{
    /**
     * Handle the manual material "saved" event.
     *
     * @return void
     */
    public function saved(ManualMaterial $manualMaterial): void
    {
        if ($manualMaterial->isDirty('manual_reference_id')) {
            DB::beginTransaction();

            $reference = ManualReference::find($manualMaterial->manual_reference_id);
            $referenceParameters = $reference->parameters;
            $manualMaterial->parameters()->whereNotIn('name', ['Длина', 'Ширина'])->forceDelete();
            foreach ($referenceParameters as $referenceParameter) {
                $manualMaterial->parametersClear()->create(
                    ['attr_id' => $referenceParameter->attr_id, 'value' => $referenceParameter->value]
                );
            }

            $manualMaterial->count_preset_attrs($reference);
            //            $manualMaterial->makeMaterialName($reference);

            DB::commit();
        }
    }
}
