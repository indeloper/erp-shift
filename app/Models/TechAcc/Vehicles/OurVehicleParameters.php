<?php

namespace App\Models\TechAcc\Vehicles;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OurVehicleParameters extends Model
{
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
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function characteristic()
    {
        return $this->belongsTo(VehicleCategoryCharacteristics::class, 'characteristic_id', 'id');
    }

    /**
     * Relation to vehicle
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vehicle()
    {
        return $this->belongsTo(OurVehicles::class, 'vehicle_id', 'id');
    }
    /** Methods */
}
