<?php

namespace App\Models\Manual;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualMaterial extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = ['name', 'description', 'category_id', 'passport_file', 'buy_cost', 'use_cost', 'manual_reference_id'];

    protected $appends = ['reference_name'];

    const ATTRS_TO_SEARCH = [
        '2' => ['Марка'],
        '3' => ['Марка', 'Диаметр'],
        '4' => ['Марка'],
        '5' => ['Толщина'],
        '6' => ['Высота h', 'Серия'],
        '7' => ['Диаметр', 'Толщина'],
        '8' => ['Диаметр', 'Толщина'],
        '9' => ['Длина стороны а', 'Длина стороны б', 'Толщина'],
        '10' => ['Марка'],
        '11' => ['Длина стороны а', 'Длина стороны б', 'Толщина', 'Марка стали'],
        '22' => ['Марка'],
        '26' => ['Марка'],
        '42' => ['Марка'],
    ];

    public function getReferenceNameAttribute()
    {
        return $this->reference->name ?? ' ';
    }

    public function parameters()
    {
        return $this->hasMany(ManualMaterialParameter::class, 'mat_id', 'id')
            ->leftJoin('manual_material_category_attributes', 'manual_material_category_attributes.id', '=', 'attr_id')
            ->select('manual_material_parameters.*', 'manual_material_category_attributes.name', 'manual_material_category_attributes.unit', 'manual_material_category_attributes.is_preset');
    }

    public function reference()
    {
        return $this->belongsTo(ManualReference::class, 'manual_reference_id', 'id');
    }

    public function parametersClear()
    {
        return $this->hasMany(ManualMaterialParameter::class, 'mat_id', 'id')->withTrashed();
    }

    public function category()
    {
        return $this->hasOne(ManualMaterialCategory::class, 'id', 'category_id');
    }

    public function related_works()
    {
        return $this->belongsToMany(ManualWork::class, 'manual_relation_material_works', 'manual_material_id', 'manual_work_id')->distinct();
    }

    public function work_relations()
    {
        return $this->hasMany(ManualRelationMaterialWork::class, 'manual_material_id', 'id')
            ->leftJoin('manual_works', 'manual_works.id', '=', 'manual_relation_material_works.manual_work_id')
            ->select('manual_relation_material_works.*', 'manual_works.name', 'manual_works.unit', 'manual_works.price_per_unit', 'manual_works.nds', 'manual_works.unit_per_days');
    }

    public function passport()
    {
        return $this->hasOne(ManualMaterialPassport::class, 'material_id', 'id');
    }

    public function convertation_parameters()
    {
        return $this->parameters()->whereHas('attribute', function ($attr) {
            $attr->where('is_preset', 1);
        })->where('manual_material_parameters.value', '!=', 'null')
            ->where('manual_material_parameters.deleted_at', null)
            ->orderBy('manual_material_parameters.id', 'desc');
    }

    public function convert_to($unit)
    {
        return $this->convertation_parameters()->whereHas('attribute', function ($attr) use ($unit) {
            $attr->where('unit', $unit);
        })->first();
    }

    public function getFirstRelatedWorkGroupIdAttribute()
    {
        $works = $this->related_works();

        if ($works->count()) {
            return $works->first()->work_group_id;
        } else {
            return 5; //let it be default
        }
    }

    public function getCategoryUnitAttribute()
    {
        return $this->category->category_unit;
    }

    public function convert_from($unit)
    {
        if ($unit == $this->category_unit) {
            return $this->convertation_parameters;
        } else {
            $currentUnit = $this->convert_to($unit);

            if ($currentUnit) {
                $convert_params = $this->convertation_parameters()->where('unit', '!=', $unit)->get();

                foreach ($convert_params as $index => $param) {
                    $param->value /= $this->convert_to($unit)->value;
                }

                $category_unit_param = (object) ['value' => (1 / $currentUnit->value), 'unit' => $this->category_unit];

                $convert_params->push($category_unit_param);
            }

            return $convert_params ?? collect();
        }
    }

    public function getConvertValueFromTo($from_unit, $to_unit)
    {
        if ($from_unit === $to_unit) {
            return 1;
        }
        $convert_param = $this->convert_from($from_unit)->where('unit', $to_unit)->first();
        if ($convert_param) {
            return $convert_param->value;
        } else {
            return 0;
        }
    }

    public function getCatFormulaName()
    {
        if ($this->category_id == 2) {
            $parameter = $this->parameters()->whereHas('attribute', function ($q) {
                $q->where('name', 'like', '%клиновидный%');
                $q->orWhere('name', 'like', '%трубошпунт%');
            })->first();

            if ($parameter) {
                if ($parameter->name == 'Трубошпунт') {
                    return 'Трубошпунт';
                } elseif ($parameter->name == 'Клиновидный') {
                    return 'Клиновидный шпунт';
                }
            }
        }

        return $this->category->name;
    }

    public function hasAttrMeter()
    {
        $attr_1 = $this->category->attributesAll()->where('unit', 'м.п')->first();
        $attr_2 = $this->category->attributesAll()->where('unit', 'м')->first();

        $exist_cat = $this->parameters()->whereAttrId($attr_1->id ?? 0)->first()->value ?? 0;
        $exist_length = $this->parameters()->whereAttrId($attr_2->id ?? 0)->first()->value ?? 0;

        if ($exist_cat || $exist_length) {
            return true;
        }

        return false;
    }

    public function makeMaterialName($reference = null)
    {
        if ($reference) {
            $attr_2 = $this->category->attributesAll()->where('name', 'Длина')->first();
            $attr_3 = $this->category->attributesAll()->where('name', 'Ширина')->first();

            $length = $this->parameters()->where('attr_id', $attr_2->id ?? 0)->first()->value ?? 0;
            $width = $this->parameters()->where('attr_id', $attr_3->id ?? 0)->first()->value ?? 0;

            $this->name = $reference->name;

            if ($this->category_id == 5 && $length && $width) {
                $this->name .= 'x'.$length.'x'.$width;
                $this->save();

                return;
            }

            if ($length) {
                $this->name .= ' '.$length.' метров';
            }

            $this->save();
        }
    }

    // key -> attr_id, value -> value
    public function createNewRelations($collection_parameters)
    {
        foreach ($collection_parameters as $item) {
            if (isset($item['value']) && isset($item['attr_id'])) {
                $this->parameters()->where('attr_id', $item['attr_id'])->delete();

                $this->parameters()->create([
                    'attr_id' => $item['attr_id'],
                    'value' => $item['value'],
                ]);
            }
        }
    }

    public function count_preset_attrs($reference)
    {
        $category_unit = $reference->category->category_unit;
        $attr_1 = $reference->category->attributesAll()->where('name', 'like', '%'.'Длина 1 '.$category_unit.'%')->first();
        $attr_2 = $reference->category->attributesAll()->where('name', 'Длина')->first();
        $attr_3 = $reference->category->attributesAll()->where('name', 'like', '%'.'Масса 1 '.$category_unit.'%')->first();

        $attr_4 = $reference->category->attributesAll()
            ->where('is_preset', 0)
            ->where(function ($query) {
                $query->where('name', 'like', '%'.'Вес 1 м.п.'.'%');
                $query->orWhere('name', 'like', '%'.'Масса 1 м.п.'.'%');
            })->first();

        $attr_5 = $reference->category->attributesAll()->where('name', 'like', '%'.'Площадь 1 '.$category_unit.'%')->first();
        $attr_6 = $reference->category->attributesAll()->where('name', 'like', '%'.'Ширина'.'%')->first();
        $attr_7 = $reference->category->attributesAll()->where('name', 'like', '%'.'Количество в 1 '.$category_unit.'%')->first();

        // only for list gk
        $attr_8 = $reference->category->attributesAll()
            ->where('is_preset', 0)
            ->where('name', 'like', '%'.'Масса 1 м2'.'%')->first();

        $length = $this->parameters()->where('attr_id', $attr_2->id ?? 0)->first()->value ?? 0;
        $length_per_weight = $this->parameters()->where('attr_id', $attr_4->id ?? 0)->first()->value ?? 0;
        if ($attr_1) {
            if ($category_unit == 'шт') {
                $this->parameters()->updateOrCreate(
                    ['attr_id' => $attr_1->id],
                    ['value' => $length]
                );
            } elseif ($category_unit == 'т') {
                if ($attr_4) {
                    $this->parameters()->updateOrCreate(
                        ['attr_id' => $attr_1->id],
                        ['value' => (1000 / $length_per_weight)]
                    );
                }
            }
        }

        if ($attr_3 && $length) {
            $this->parameters()->updateOrCreate(
                ['attr_id' => $attr_3->id],
                ['value' => (float) str_replace(',', '.', ((float) $length * (float) $length_per_weight) / 1000)]
            );
        } elseif ($attr_3 && ! $length) {
            $unitWeight = $reference->parameters()->where('attr_id', $attr_3->id ?? 0)->first()->value ?? 0;

            $this->parameters()->updateOrCreate(
                ['attr_id' => $attr_3->id],
                ['value' => (float) str_replace(',', '.', $unitWeight)]
            );
        }

        if ($attr_5) {
            $weight = $reference->parameters()->where('attr_id', $attr_8->id)->first()->value ?? 0;
            $square = 1000 / $weight;

            $this->parameters()->updateOrCreate(
                ['attr_id' => $attr_5->id],
                ['value' => $square]
            );
        }

        if ($attr_7 && $length && $length_per_weight && ! $attr_8) {
            $unit_count = 1 / (($length * $length_per_weight) / 1000);

            $this->parameters()->updateOrCreate(
                ['attr_id' => $attr_7->id],
                ['value' => $unit_count]
            );
        }

        if ($attr_5 && $attr_8) {
            //unit square
            $param1 = $reference->parameters()->where('attr_id', $attr_5->id)->first()->value ?? 0;
            // weight 1 m^2
            $param2 = $reference->parameters()->where('attr_id', $attr_8->id)->first()->value ?? 0;
            // length and weight
            $param3 = $this->parameters()->where('attr_id', $attr_2->id)->first()->value ?? 0;
            $param4 = $this->parameters()->where('attr_id', $attr_6->id)->first()->value ?? 0;
            // m^2
            $square = ($param3 * $param4) * 0.001 * 0.001;
            $weight_one_list = ($param2 * $square) / 1000;

            if ($weight_one_list) {
                $this->parameters()->updateOrCreate(
                    ['attr_id' => $attr_7->id],
                    ['value' => 1 / $weight_one_list]
                );
            }
        }
    }

    public function createMaterial($attributes, $category_id)
    {
        $etalon_id = array_filter($attributes, function ($item) {
            if ($item['id'] == 'etalon') {
                return true;
            }
        })[0]['value'];
        $attributes = array_filter($attributes, function ($item) {
            if ($item['id'] != 'etalon') {
                return true;
            }
        });
        $reference = ManualReference::find($etalon_id);

        if (! $reference) {
            throw new \Exception('Создайте эталон!', 415);
        }
        $reference->load(['parameters.attribute']);
        $reference_attrs = $reference->parameters()->whereHas('attribute', function ($q) use ($category_id) {
            $q->whereIn('name', self::ATTRS_TO_SEARCH[$category_id]);
        })->get()->toArray();

        $material = ManualMaterial::where('category_id', $category_id);
        $collection_parameters = array_merge($attributes, $reference_attrs);
        foreach ($attributes as $attr_key => $item) {
            if (! is_null($item['value'])) {
                if ($item['name'] == 'Длина') {
                    $item['value'] = str_replace(',', '.', $item['value']);
                    $attributes[$attr_key]['value'] = str_replace(',', '.', $item['value']);
                }
                $material->whereHas('parameters', function ($q) use ($item) {
                    $q->where('attr_id', $item['id']);
                    $q->where('value', $item['value']);
                });
            } else {
                $material->whereDoesntHave('parameters', function ($q) use ($item) {
                    $q->where('attr_id', $item['id']);
                });
            }
        }
        $material->where('manual_reference_id', $reference->id);
        $material = $material->first();

        if (! $material) {

            $new_material = new ManualMaterial();
            $new_material->category_id = $category_id;
            $new_material->save();

            $reference->load('parameters', 'category.attributesAll');

            foreach ($reference->parameters as $parameter) {

                $new_material->parameters()->create([
                    'attr_id' => $parameter->attr_id,
                    'value' => $parameter->value,
                ]);
            }

            $new_material->createNewRelations($collection_parameters);
            $new_material->fresh();
            $new_material->count_preset_attrs($reference);
            $new_material->makeMaterialName($reference);
            $new_material->manual_reference_id = $reference->id;
            $new_material->save();

            return $new_material;
        } else {

            return $material;
        }
    }
}
