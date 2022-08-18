<?php
/**  * @mixin ..\Eloquent  */
namespace App\Models\q3wMaterial\operations;

use App\Models\q3wMaterial\q3wMaterialStandard;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class q3wOperationMaterial extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable;

    protected $guarded = array('id');

    public function standard(): HasOne
    {
        return $this->hasOne(q3wMaterialStandard::class, 'id', 'standard_id');
    }
}

