<?php

namespace App\Models\WorkVolume;

use App\Models\Manual\ManualMaterialParameter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class WorkVolumeWorkMaterial extends Model
{
    protected $fillable = ['wv_work_id', 'wv_material_id'];

    public function combine_pile()
    {
        $materials = WorkVolumeMaterial::where('combine_id', $this->combine_id)->pluck('manual_material_id');

        $name = 'С'.ManualMaterialParameter::whereIn('mat_id', $materials)
            ->whereNotIn('attr_id', [92])
            ->where('attr_id', '93')
            ->select(DB::raw('sum(value) as value'))->first()->value * 10 .'.'.ManualMaterialParameter::whereIn('mat_id', $materials)->whereNotIn('attr_id', [92])->where('attr_id', '95')->first()->value.'-СВ';

        return $name;
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(WorkVolumeWork::class, 'wv_work_id', 'id');
    }
}
