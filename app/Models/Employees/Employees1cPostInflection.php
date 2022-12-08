<?php
/**  * @mixin ..\Eloquent  */
namespace App\Models\Employees;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employees1cPostInflection extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable;

    protected $table = 'employees_1c_post_inflections';
    protected $guarded = array('id');
}
