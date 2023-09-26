<?php
/**  * @mixin ..\Eloquent  */
namespace App\Models\OneC;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employees1cSubdivision extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable;

    protected $table = 'employees_1c_subdivisions';
    protected $guarded = array('id');
}
