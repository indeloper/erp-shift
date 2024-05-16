<?php

namespace App\Models\WorkVolume;

use App\Models\Manual\ManualMaterialParameter;
use Illuminate\Database\Eloquent\Model;

class WorkVolumeMaterialComplect extends Model //this is like manual for materials, but for complects
{
    protected $fillable = [
        'name',
        'wv_material_id',
        'work_volume_id',
    ];

    public function w_v_instance()
    {
        return $this->morphOne(WorkVolumeMaterial::class, 'manual', 'material_type', 'manual_material_id');
    }

    public function getParametersAttribute()
    {
        return $this->w_v_instance->parts->pluck('manual.parameters')->flatten();
    }

    public function category()
    {
        return $this->w_v_instance->parts->first()->manual->category();
    }

    public function getCategoryIdAttribute()
    {
        return $this->category->id;
    }

    /**
     * this dummy convert_to mocks this method for Work Volumes
     *
     * @param  mixed  ...$attrs
     * @return ManualMaterialParameter
     */
    public function convert_to(...$attrs)
    {
        return new ManualMaterialParameter(['value' => 1]);
    }

    public function getRelatedWorksAttribute()
    {
        return $this->w_v_instance->parts->pluck('manual.related_works')->flatten();
    }

    public function getWorkRelationsAttribute()
    {
        return $this->w_v_instance->parts->pluck('manual.work_relations')->flatten();
    }
}
