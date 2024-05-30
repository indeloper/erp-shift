<?php

namespace App\Observers\Manual;

use App\Models\Manual\ManualReference;
use Illuminate\Support\Facades\DB;

class ManualReferenceObserver
{
    public function saved(ManualReference $ref)
    {
        DB::beginTransaction();
        $params = $ref->parameters()->get();
        foreach ($ref->materials as $material) {
            foreach ($params as $param) {
                $material->parameters()->where('attr_id', $param->attr_id)->delete();
                $material->parameters()->create([
                    'attr_id' => $param->attr_id,
                    'value' => $param->value,
                ]);
            }
        }
        DB::commit();

    }
}
