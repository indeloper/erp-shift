<?php

namespace App\Models\Manual;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualReference extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'category_id'];

    public function parameters(): HasMany
    {
        return $this->hasMany(ManualReferenceParameter::class, 'manual_reference_id', 'id')
            ->leftJoin('manual_material_category_attributes', 'manual_material_category_attributes.id', '=', 'attr_id')
            ->select('manual_reference_parameters.*', 'manual_material_category_attributes.name', 'manual_material_category_attributes.unit')
            ->withTrashed();
    }

    public function parametersClear(): HasMany
    {
        return $this->hasMany(ManualReferenceParameter::class, 'manual_reference_id', 'id')->withTrashed();
    }

    public function parametersClearNotDeleted(): HasMany
    {
        return $this->hasMany(ManualReferenceParameter::class, 'manual_reference_id', 'id');
    }

    public function category(): HasOne
    {
        return $this->hasOne(ManualMaterialCategory::class, 'id', 'category_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ManualMaterial::class, 'manual_reference_id', 'id');
    }

    public function createNewRelations($collection_parameters)
    {
        foreach ($collection_parameters as $attr_id => $value) {
            if ($value) {
                $this->parameters()->where('attr_id', $attr_id)->delete();

                $this->parameters()->create([
                    'attr_id' => $attr_id,
                    'value' => $value,
                ]);
            }
        }
    }
}
