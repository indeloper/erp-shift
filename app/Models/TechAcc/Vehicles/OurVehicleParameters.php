<?php

namespace App\Models\TechAcc\Vehicles;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OurVehicleParameters extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'characteristic_id',
        'value',
    ];

    /** Custom getters */
    /** Relations */

    /**
     * Relation to category characteristic
     */
    public function characteristic(): BelongsTo
    {
        return $this->belongsTo(VehicleCategoryCharacteristics::class, 'characteristic_id', 'id');
    }

    /**
     * Relation to vehicle
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(OurVehicles::class, 'vehicle_id', 'id');
    }
    /** Methods */
}
