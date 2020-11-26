<?php

namespace App\models\q3wMaterial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class q3wMaterialSnapshot extends Model
{
    use SoftDeletes;

    protected $guarded = array('id');

    public function takeSnapshot($operation, $projectObject)
    {
        $this->operation_id = $operation->id;
        $this->project_object_id = $projectObject->id;

        $this->save();

        $actualMaterials = q3wMaterial::where('project_object', '=', $projectObject->id)->get();
        foreach ($actualMaterials as $actualMaterial) {
            $snapshotMaterial = new q3wMaterialSnapshotMaterial([
                'snapshot_id' => $this->id,
                'standard_id' => $actualMaterial->standard_id,
                'amount' => $actualMaterial->amount,
                'quantity' => $actualMaterial->quantity
            ]);

            $snapshotMaterial->save();
        }
    }
}
