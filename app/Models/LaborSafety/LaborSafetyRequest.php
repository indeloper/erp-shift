<?php
/**  * @mixin ..\Eloquent  */
namespace App\Models\LaborSafety;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class LaborSafetyRequest extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable;

    protected $guarded = array('id');
}
