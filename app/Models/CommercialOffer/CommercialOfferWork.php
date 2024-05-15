<?php

namespace App\Models\CommercialOffer;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Contractors\Contractor;
use App\Models\Contractors\ContractorFile;
use App\Models\WorkVolume\WorkVolumeWork;
use App\Traits\Reviewable;
use Illuminate\Database\Eloquent\Model;

class CommercialOfferWork extends Model
{
    use Reviewable;

    protected $fillable = [
        'user_id',
        'work_volume_work_id',
        'commercial_offer_id',
        'count',
        'unit',
        'term',
        'price_per_one',
        'result_price',
        'subcontractor_file_id',
        'is_hidden',
        'order',
    ];

    protected $appends = ['shown_materials'];
    /*
     * we need this only for dynamic prices, terms, order, is_hidden, subcontractor files
     *
     * we can't change work-material relations, manuals etc.
     * */

    public function work_volume_parent(): BelongsTo
    {
        return $this->belongsTo(WorkVolumeWork::class, 'work_volume_work_id', 'id');
    }

    public function relations()
    {
        return $this->work_volume_parent->relations();
    }

    public function materials() //all materials. here raw materials (without complects)
    {
        return $this->work_volume_parent->materials();
    }

    public function manual()
    {
        return $this->work_volume_parent->manual();
    }

    public function complects_relations()
    {
        return $this->work_volume_parent->complects_relations();
    }

    public function getWorkGroupIdAttribute()
    {
        return $this->manual->work_group_id;
    }

    public function getShownMaterialsAttribute()
    {
        return $this->work_volume_parent->shown_materials;
    }

    /**
     * Relation from work to subcontractor file
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function subcontractor_file(): HasOne
    {
        return $this->hasOne(ContractorFile::class, 'id', 'subcontractor_file_id');
    }

    public function commercial_offer(): BelongsTo
    {
        return $this->belongsTo(CommercialOffer::class);
    }

    /**
     * Fast getter for work subcontractor
     *
     * @return Contractor | null
     */
    public function subcontractor()
    {
        return $this->subcontractor_file->contractor ?? null;
    }
}
