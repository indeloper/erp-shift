<?php

namespace App\Models\Manual;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualMaterialParameter extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['attr_id', 'mat_id', 'value'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($parameter) {
            $comma_replaced = str_replace(',', '.', $parameter->value);
            if (is_numeric($comma_replaced)) {
                $parameter->value = (float) $comma_replaced;
            }
        });

        static::creating(function ($parameter) {
            $comma_replaced = str_replace(',', '.', $parameter->value);
            if (is_numeric($comma_replaced)) {
                $parameter->value = (float) $comma_replaced;
            }
        });
    }

    public static function getMaterialsFromValues($values, $category)
    {
        $parameters = ManualMaterialParameter::whereIn('value', $values)->get();

        $materials = ManualMaterial::whereIn('manual_materials.id', $parameters->pluck('mat_id'))->where('category_id', $category)
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
            ->select('manual_materials.*', 'manual_materials.id as manual_material_id', 'manual_material_categories.name as category_name');

        return $materials;
    }

    public function attribute()
    {
        return $this->belongsTo(ManualMaterialCategoryAttribute::class, 'attr_id', 'id');
    }
}
