<?php

namespace App\Models\HumanResources;

use App\Traits\HasAuthor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TariffRates extends Model
{
    use SoftDeletes, HasAuthor;

    protected $fillable = ['name', 'type', 'user_id'];

    const TYPES = [
        1 => 'Список работ',
        2 => 'Список сделок',
    ];

    // Local Scopes

    public function scopeWorkTypes(Builder $q)
    {
        return $q->where('type', 1);
    }

    public function scopeDealTypes(Builder $q)
    {
        return $q->where('type', 2);

    }

    // Custom getters

    // Relations

    // Methods
}
