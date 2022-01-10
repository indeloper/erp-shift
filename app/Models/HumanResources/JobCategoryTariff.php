<?php

namespace App\Models\HumanResources;

use App\Traits\{HasAuthor, Logable};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobCategoryTariff extends Model
{
    use SoftDeletes, HasAuthor, Logable;

    protected $fillable = ['job_category_id', 'tariff_id', 'rate', 'user_id'];

    protected $appends = ['name'];

    // Local Scopes

    // Custom getters
    /**
     * Function get job category tariff name
     * @return string|null
     */
    public function getNameAttribute()
    {
        return $this->tariff->name ?? null;
    }

    // Relations
    /**
     * Relation for job category tariff rate
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tariff()
    {
        return $this->hasOne(TariffRates::class, 'id', 'tariff_id');
    }

    // Methods
}
