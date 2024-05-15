<?php

namespace App\Models\Manual;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualRelationMaterialWork extends Model
{
    use SoftDeletes;

    protected $fillable = ['manual_work_id', 'manual_material_id'];

    public function materials(): HasMany
    {
        return $this->hasMany(ManualMaterial::class, 'id', 'manual_material_id');
    }

    public function works(): HasMany
    {
        return $this->hasMany(ManualWork::class, 'id', 'manual_work_id');
    }
}
