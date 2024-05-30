<?php

namespace App\Models\Manual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualNodes extends Model
{
    use SoftDeletes;

    protected $fillable = ['node_category_id', 'name', 'description'];

    public function node_materials(): HasMany
    {
        return $this->hasMany(ManualNodeMaterials::class, 'node_id', 'id');
    }

    public function node_category(): HasOne
    {
        return $this->hasOne(ManualNodeCategories::class, 'id', 'node_category_id');
    }

    public function materials(): HasManyThrough
    {
        return $this->hasManyThrough(ManualMaterial::class, ManualNodeMaterials::class, 'manual_material_id', 'id');
    }
}
