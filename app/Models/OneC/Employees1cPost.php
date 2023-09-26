<?php
/**  * @mixin ..\Eloquent  */
namespace App\Models\OneC;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employees1cPost extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable;

    protected $table = 'employees_1c_posts';
    protected $guarded = array('id');
}
