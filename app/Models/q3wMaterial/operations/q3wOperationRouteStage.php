<?php
/**  * @mixin ..\Eloquent  */
namespace App\Models\q3wMaterial\operations;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class q3wOperationRouteStage extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable;

    protected $guarded = array('id');

    public function routeStageTypes() {
        return $this->belongsTo(q3wOperationRouteStage::class, 'id', 'operation_route_stage_type_id');
    }

    public function scopeCompleted($query) {
        return $query->where('operation_route_stage_type_id', 2);
    }
}

