<?php

namespace App\Models\Manual;

use App\Traits\Documentable;
use App\Traits\Reviewable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ManualMaterialCategory extends Model
{
    use Documentable, Reviewable, SoftDeletes;
    use HasFactory;

    protected $fillable = ['name', 'description', 'category_unit', 'formula'];

    protected $appends = ['unit_show'];

    public function attributes(): HasMany
    {
        return $this->hasMany(ManualMaterialCategoryAttribute::class, 'category_id', 'id')
            ->when((! Auth::user() || Auth::user()->id != 1), function ($query) {
                return $query->where('is_display', 1);
            });
    }

    public $categories_unit_show = [
        4 => 'м.п',
        5 => 'м2',
        7 => 'м.п',
        8 => 'м.п',
        9 => 'м.п',
        11 => 'м.п',
        16 => 'шт',
        19 => 'м3',
        21 => 'м3',
    ];

    public function attributesAll(): HasMany
    {
        return $this->hasMany(ManualMaterialCategoryAttribute::class, 'category_id', 'id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ManualMaterial::class, 'category_id', 'id');
    }

    public function references(): HasMany
    {
        return $this->hasMany(ManualReference::class, 'category_id', 'id');
    }

    public function related_works(): BelongsToMany
    {
        return $this->belongsToMany(ManualWork::class, 'manual_material_category_relation_to_works', 'manual_material_category_id', 'work_id');
    }

    public function getUnitShowAttribute()
    {
        return $this->categories_unit_show[$this->id] ?? $this->category_unit;
    }

    public function needAttributes()
    {
        $formula = $this->formula;
        $inputs = [];

        if ($formula) {
            $replaced = str_replace('<cat>name</cat>', 'name', $formula);

            $text_chunks = preg_split('{<attr>}', $replaced);
            $text_chunks = implode(' ', $text_chunks);
            $text_chunks = preg_split('{</attr>}', $text_chunks);
            $parsedArrayIds = explode(' ', str_replace('  ', ' ', implode('', $text_chunks)));

            $needAttributes = [];
            foreach ($parsedArrayIds as $key => $item) {
                if ((int) $item) {
                    $needAttributes[] = (int) $item;
                }
            }
            $attrs = $this->attributesAll()->whereIn('name', ['Длина', 'Ширина'])->whereIn('id', $needAttributes)->get();
            foreach ($attrs as $attr) {
                $inputs[] = ['id' => $attr->id, 'unit' => '', 'is_required' => $attr->is_required, 'name' => $attr->name, 'value' => 'smth', 'category_id' => null];
            }

            $inputs[] = ['id' => 'etalon', 'unit' => '', 'is_required' => 1, 'name' => 'Эталон', 'value' => '', 'category_id' => $this->id];

            return $inputs;
        }

        return [];
    }
}
