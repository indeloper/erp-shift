<?php

/**  * @mixin ..\Eloquent  */

namespace App\Models\q3wMaterial\operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class q3wOperationRouteStageType extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function routeStages(): HasMany
    {
        return $this->hasMany(q3wOperationRouteStage::class, 'operation_route_stage_type_id', 'id');
    }
}
