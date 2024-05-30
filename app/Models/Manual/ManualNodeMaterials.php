<?php

namespace App\Models\Manual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualNodeMaterials extends Model
{
    use SoftDeletes;

    protected $fillable = ['node_id', 'manual_material_id', 'count', 'unit'];

    public function materials(): HasOne
    {
        return $this->hasOne(ManualMaterial::class, 'id', 'manual_material_id');
    }
}
