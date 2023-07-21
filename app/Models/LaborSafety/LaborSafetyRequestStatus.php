<?php
/**  * @mixin ..\Eloquent  */
namespace App\Models\LaborSafety;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaborSafetyRequestStatus extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable;

    protected $guarded = array('id');
}
