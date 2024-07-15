<?php

namespace App\Models\CommercialOffer;

use App\Models\Contractors\Contractor;
use App\Models\Contractors\ContractorFile;
use App\Models\Manual\ManualMaterialParameter;
use App\Models\WorkVolume\WorkVolumeMaterial;
use App\Services\Commerce\SplitService;
use App\Traits\Reviewable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class CommercialOfferMaterialSplit extends Model
{
    use Reviewable;

    protected $fillable = ['count', 'unit', 'time', 'security_pay', 'man_mat_id', 'type', 'com_offer_id', 'price_per_one', 'result_price', 'security_price_result', 'material_type'];

    protected $appends = ['human_rent_time'];

    public $types = [
        'Продажа',
        'Продажа с обратным выкупом',
        'Аренда',
        'Аренда с обеспечительным платежём',
        'Давальческий',
    ];

    public $english_types = [
        1 => 'sale',
        2 => 'buyback',
        3 => 'rent',
        4 => 'security',
        5 => 'given',
    ];

    public $modification_types = [
        2,
        4,
    ];

    public $parent_types = [
        1,
        3,
        5,
    ];

    public function manual(): MorphTo
    {
        return $this->morphTo(null, 'material_type', 'man_mat_id', 'id')->withDefault(function ($manual) {
            $manual->id = $this->manual_material_id;
            $manual->name = 'Объединённый материал';
        })->withTrashed();
    }

    public function getParametersAttribute()
    {
        return $this->WV_material->parameters;
    }

    public function getWorkVolumeIdAttribute()
    {
        return CommercialOffer::where('id', $this->com_offer_id)->first()->work_volume_id;
    }

    public function WV_material(): BelongsTo
    {
        return $this->belongsTo(WorkVolumeMaterial::class, 'man_mat_id', 'manual_material_id');
    }

    public function getSiblings()
    {
        return CommercialOfferMaterialSplit::where('man_mat_id', $this->man_mat_id)->where('com_offer_id', $this->com_offer_id)->whereNotIn('type', $this->modification_types)->where('id', '!=', $this->id)->select('id', 'type', 'time', 'count')->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany | CommercialOfferMaterialSplit
     */
    public function children(): HasMany
    {
        return $this->hasMany(CommercialOfferMaterialSplit::class, 'parent_id')->with('children');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne | CommercialOfferMaterialSplit
     */
    public function parent(): HasOne
    {
        return $this->hasOne(CommercialOfferMaterialSplit::class, 'id', 'parent_id')->with('parent')->withDefault($this->attributesToArray());

    }

    public function buyback(): HasOne
    {
        return $this->hasOne(CommercialOfferMaterialSplit::class, 'parent_id')->where('type', 2);
    }

    public function security(): HasOne
    {
        return $this->hasOne(CommercialOfferMaterialSplit::class, 'parent_id')->where('type', 4);
    }

    public function contractor(): HasOneThrough
    {
        return $this->hasOneThrough(Contractor::class, ContractorFile::class, 'id', 'id', 'subcontractor_file_id', 'contractor_id');
    }

    public function getTypeNameAttribute()
    {
        return $this->types[$this->type - 1].($this->time ? " {$this->time} мес." : '');
    }

    public function getCommentSuffixAttribute()
    {
        return $this->comment ? " ({$this->comment})" : '';
    }

    public function getNameAttribute()
    {
        $mat_name = $this->manual->name ?? 'Материал';

        return $mat_name.' '.$this->type_name.$this->comment_suffix;
    }

    public function getHumanRentTimeAttribute()
    {
        if ($this->time) {
            return (new SplitService())->makeHumanRentTime($this->time);
        } else {
            return '';
        }
    }

    public function combine_pile()
    {
        $materials = WorkVolumeMaterial::where('combine_id', $this->WV_material->combine_id)->pluck('manual_material_id');

        $name = 'С'.ManualMaterialParameter::whereIn('mat_id', $materials)
            ->whereNotIn('attr_id', [92])
            ->where('attr_id', '93')
            ->select(DB::raw('sum(value) as value'))->first()->value * 10 .'.'.ManualMaterialParameter::whereIn('mat_id', $materials)->whereNotIn('attr_id', [92])->where('attr_id', '95')->first()->value.'-СВ';

        return $name;
    }

    public function subcontractor_file(): HasOne
    {
        return $this->hasOne(ContractorFile::class, 'id', 'subcontractor_file_id');
    }

    public function scopeOfType(Builder $query, $type, $time = null)
    {
        return $query->where('type', $type)->where('time', $time);
    }

    public function decreaseCountBy($count)
    {
        $diff = number_format($this->count - $count, 3);

        if ($diff <= 0) {
            $this->deleteModifications();
            $this->children()->update(['parent_id' => null]);
            $this->delete();

            return null;
        } else {
            $this->count -= $count;
            foreach ($this->children as $child) {
                if ($child->count > $this->count) {
                    $child->count = $this->count;
                    $child->save();
                }
            }
            $this->save();

            return $this;
        }
    }

    public function deleteModifications()
    {
        $this->children()->whereIn('type', $this->modification_types)->delete();
    }
}
