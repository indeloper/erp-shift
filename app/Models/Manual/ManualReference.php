<?php

namespace App\Models\Manual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualReference extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'category_id'];

    public function parameters()
    {
        return $this->hasMany(ManualReferenceParameter::class, 'manual_reference_id', 'id')
            ->leftJoin('manual_material_category_attributes', 'manual_material_category_attributes.id','=', 'attr_id')
            ->select('manual_reference_parameters.*', 'manual_material_category_attributes.name', 'manual_material_category_attributes.unit')
            ->withTrashed();
    }

    public function parametersClear()
    {
        return $this->hasMany(ManualReferenceParameter::class, 'manual_reference_id', 'id')->withTrashed();
    }


    public function parametersClearNotDeleted()
    {
        return $this->hasMany(ManualReferenceParameter::class, 'manual_reference_id', 'id');
    }


    public function category()
    {
        return $this->hasOne(ManualMaterialCategory::class, 'id', 'category_id');
    }

    public function materials()
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
