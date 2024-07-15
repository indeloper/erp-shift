<?php

namespace App\Models\WorkVolume;

use App\Models\CommercialOffer\CommercialOffer;
use App\Models\Manual\ManualMaterialParameter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class WorkVolumeMaterial extends Model
{
    protected $fillable = ['user_id', 'work_volume_id', 'manual_material_id', 'is_our', 'time', 'count', 'unit', 'subcontractor_id', 'node_id', 'is_node', 'material_type'];

    //    protected $with = ['manual'];

    protected $appends = ['name']; //,'category_unit']; //, 'is_complect', 'work_group_id'];

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function manual(): MorphTo
    {
        return $this->morphTo(null, 'material_type', 'manual_material_id', 'id')->withDefault(function ($manual) {
            $manual->id = $this->manual_material_id;
            $manual->name = 'Объединённый материал';
        })->withTrashed();
    }

    public function getIsComplectAttribute()
    {
        return $this->material_type === 'complect' ? 1 : 0;
    }

    public function getNameAttribute()
    {
        return $this->manual->name;
    }

    public function getCategoryUnitAttribute()
    {
        return $this->manual->category->category_unit;
    }

    public function getWorkGroupIdAttribute()
    {
        $category_id = $this->manual->category_id;

        $work_group_id = $this->get_first_relation_work->first();
        if ($category_id == 12 or $category_id == 14) {
            return 2;
        } elseif (isset($work_group_id->work->manual->work_group_id)) {
            return $work_group_id->work->manual->work_group_id;
        } elseif (in_array($category_id, [3, 4, 5, 6, 7, 8, 9, 11])) {
            return 4;
        } elseif (in_array($category_id, [2, 10])) {
            return 1;
        } else {
            return $this->manual->first_related_work_group_id ?? 4;
        }
    }

    public function getParametersAttribute()
    {
        return $this->manual->parameters;
    }

    public function getCommercialOfferIdAttribute()
    {
        return CommercialOffer::where('work_volume_id', $this->work_volume_id)->pluck('id')->first();
    }

    public function combine_pile()
    {
        $materials = WorkVolumeMaterial::where('combine_id', $this->combine_id)->pluck('manual_material_id');

        $name = 'С'.ManualMaterialParameter::whereIn('mat_id', $materials)
            ->whereNotIn('attr_id', [92])
            ->where('attr_id', '93')
            ->select(DB::raw('sum(value) as value'))->first()->value * 10 .'.'.ManualMaterialParameter::whereIn('mat_id', $materials)->whereNotIn('attr_id', [92])->where('attr_id', '95')->first()->value.'-СВ';

        return $name;
    }

    public function complect(): BelongsTo
    {
        return $this->belongsTo(WorkVolumeMaterial::class, 'complect_id', 'id');
    }

    public function works(): BelongsToMany
    {
        return $this->belongsToMany(WorkVolumeWork::class, 'work_volume_work_materials', 'wv_material_id', 'wv_work_id');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(WorkVolumeMaterial::class, 'complect_id', 'id');
    }

    public function detach_parts()
    {
        $this->parts->each(function ($part) {
            $part->complect_id = null;
            $part->save();
        });

        return true;
    }

    public function destroy_complect()
    {
        $this->detach_parts();

        $this->delete();
    }

    public function get_first_relation_work(): HasMany
    {
        return $this->hasMany(WorkVolumeWorkMaterial::class, 'wv_material_id', 'id');
    }

    public function convertCountTo($unitName)
    {
        $conversion_parameter = $this->parameters->where('unit', $unitName)->first() ? $this->parameters->where('unit', $unitName)->first()->value : null;

        return $this->count * ($conversion_parameter ?? 1);
    }
}
