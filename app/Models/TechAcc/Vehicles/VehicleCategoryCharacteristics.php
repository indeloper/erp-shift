<?php

namespace App\Models\TechAcc\Vehicles;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleCategoryCharacteristics extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'short_name',
        'unit',
        'show',
        'required',
    ];

    protected function casts(): array
    {
        return [
            'show' => 'boolean',
            'required' => 'boolean',
        ];
    }

    const SHOW = [
        0 => 'show off',
        1 => 'show on',
    ];

    /**
     * Relation for vehicle category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategories::class, 'category_id', 'id');
    }

    /**
     * Relation for vehicle category
     */
    public function parameters(): HasMany
    {
        return $this->hasMany(OurVehicleParameters::class, 'characteristic_id', 'id');
    }
}
