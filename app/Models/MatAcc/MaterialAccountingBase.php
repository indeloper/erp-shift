<?php

namespace App\Models\MatAcc;

use App\Models\Manual\ManualMaterialParameter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProjectObject;
use App\Models\Manual\ManualMaterial;

use \Carbon\Carbon;
use Illuminate\Http\Request;

class MaterialAccountingBase extends Model
{
    protected $fillable = [
        'object_id',
        'manual_material_id',
        'date',
        'count',
        'unit',
        'used',
    ];

    protected $appends = ['round_count', 'convert_params', 'material_name'];

    protected $casts = ['used' => 'boolean'];

    public static $filter = [
        ['id' => 0, 'text' => 'Объект', 'db_name' => 'object_id'],
        ['id' => 1, 'text' => 'Материал', 'db_name' => 'manual_material_id'],
        ['id' => 3, 'text' => 'Эталон', 'db_name' => 'manual_reference_id'],
    ];

    /**
     * Scope for operations index page
     * @param Builder $query
     * @return Builder
     */
    public function scopeIndex(Builder $query)
    {
        $query->where('date', Carbon::now()->format('d.m.Y'))
            ->with('object', 'material.parameters.attribute', 'material.convertation_parameters')
            ->where('count', '>', 0)
            ->take(20);

        return $query;
    }

    public function getRoundCountAttribute()
    {
        return number_format(round($this->count, 3), 3);
    }

    /**
     * This getter return base material name
     * with optional 'Б/У' label
     * @return string
     */
    public function getMaterialNameAttribute()
    {
        return $this->material->name . ($this->used ? ' Б/У' : '');
    }

    public function object()
    {
        return $this->belongsTo(ProjectObject::class, 'object_id', 'id');
    }

    public function operations()
    {
        return $this->belongsTo(MaterialAccountingOperation::class, 'object_id_to', 'object_id_from');
    }

    public function material()
    {
        return $this->belongsTo(ManualMaterial::class, 'manual_material_id', 'id')
            ->leftJoin('manual_material_categories', 'manual_material_categories.id','=', 'manual_materials.category_id')
            ->select('manual_materials.*', 'manual_material_categories.id as cat_id', 'manual_material_categories.category_unit')
            ->withTrashed();
    }

    function backdating($materials, Carbon $back_date, $object_id)
    {
        for ($date = $back_date; $date->lte(Carbon::today()); $date->addDay()) {
            $dates[] = $date->format('d.m.Y');
        }

        foreach ($materials as $material) {
            foreach ($dates as $key => $date) {
                $base = MaterialAccountingBase::firstOrNew(['object_id' => $object_id, 'manual_material_id' => $material['material_id'], 'date' => Carbon::parse($date)->format('d.m.Y')]);
                if (count($dates) > $key + 1) {
                    $base->transferred_today = 1;
                }
                $base->count += $material['material_count'];
                $base->save();
            }
        }
    }

    public function getConvertParamsAttribute()
    {
        if (isset($this->material)) {
            return $this->material->convert_from($this->unit);
        } else {
            return collect();
        }
    }


    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    /**
     * Function make used material bases from new
     * and new from used material bases
     * @param string $state
     * @param Request $request
     * @return void
     */
    public function moveTo(string $state, Request $request): void
    {
        $used = $state === 'new' ? 0 : 1;
        $baseCount = $this->count;
        $count = $request->count;
        $baseNewCount = round($baseCount - $count, 3);
        $this->update(['count' => $baseNewCount]);
        $existedBase = MaterialAccountingBase::where('object_id', $this->object_id)->where('manual_material_id', $this->manual_material_id)->where('date', Carbon::today()->format('d.m.Y'))->where('used', $used)->first();

        if ($existedBase) {
            $existedCount = $existedBase->count;
            if ($existedBase->unit != $this->unit) {
                $count = $count * $this->convert_params->where('unit', $existedBase->unit)->first()->value ?? 0;
            }

            if ($count == 0) {
                return;
            }

            $existedBase->update(['count' => round($existedCount + round($count, 3), 3)]);
        } else {
            $newBase = $this->replicate();
            $newBase->save();
            $newBase->update(['count' => round($count, 3), 'used' => $used]);
        }
    }
}
