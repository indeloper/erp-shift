<?php

namespace App\Models\WorkVolume;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class WVWorkMaterialComplect extends Model
{
    protected $fillable = [
        'complect_name',
        'work_volume_id',
        'wv_work_id',
    ];

    public function complects(): HasMany
    {
        return $this->hasMany(WorkVolumeMaterialComplect::class, 'name', 'complect_name')
            ->leftJoin('work_volume_materials', 'work_volume_materials.id', '=', 'work_volume_material_complects.wv_material_id')
            ->leftJoin('manual_materials', 'manual_materials.id', '=', 'work_volume_materials.manual_material_id')
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
            ->select('work_volume_material_complects.*', 'work_volume_material_complects.id as complect_id', 'manual_material_categories.category_unit', 'work_volume_materials.*',
                DB::raw('sum(count) as count'))
            ->groupBy('name');
    }
}
