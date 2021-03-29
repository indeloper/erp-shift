<?php
/**  * @mixin ..\Eloquent  */
namespace App\Models\q3wMaterial;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class q3wMaterialType extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable;

    protected $guarded = array('id');

    public function measureUnits() {
        return $this->belongsTo(q3wMeasureUnit::class, 'measure_unit', 'id');
    }
}
