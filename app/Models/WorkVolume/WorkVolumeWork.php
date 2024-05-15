<?php

namespace App\Models\WorkVolume;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Contractors\ContractorFile;
use App\Models\Manual\ManualWork;
use App\Traits\Reviewable;
use Illuminate\Database\Eloquent\Model;

class WorkVolumeWork extends Model
{
    use Reviewable;

    protected $appends = ['shown_materials'];

    protected $guarded = [];

    public function materials() //all materials. here raw materials (without complects)
    {
        return $this->belongsToMany(WorkVolumeMaterial::class, 'work_volume_work_materials', 'wv_work_id', 'wv_material_id');
    }

    public function getShownMaterialsAttribute() //materials to show. complect parts is replaced with parent complect
    {
        $raw_materials = $this->materials->where('work_volume_id', $this->work_volume_id);
        $materials = collect([]);

        foreach ($raw_materials as $raw) {
            $material = $raw;
            if ($raw->complect_id) {
                if (! $raw->complect) {
                    $raw->complect_id = null;
                    $raw->save();
                } else {
                    $material = $raw->complect;
                }
            }

            if (! in_array($material->id, $materials->pluck('id')->toArray())) {
                $materials->push($material);
            }
        }

        return collect($materials);
    }

    public function relations(): HasMany
    {
        return $this->hasMany(WorkVolumeWorkMaterial::class, 'wv_work_id', 'id');
    }

    public function manual(): BelongsTo
    {
        return $this->belongsTo(ManualWork::class, 'manual_work_id', 'id')->withTrashed();
    }

    public function complects_relations(): HasMany
    {
        return $this->hasMany(WVWorkMaterialComplect::class, 'wv_work_id', 'id');
    }

    /**
     * Relation from work to subcontractor file
     */
    public function subcontractor_file(): HasOne
    {
        return $this->hasOne(ContractorFile::class, 'id', 'subcontractor_file_id');
    }

    /**
     * Fast getter for work subcontractor
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne | null
     */
    public function subcontractor(): HasOne
    {
        return $this->subcontractor_file->contractor ?? null;
    }
}
