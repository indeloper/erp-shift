<?php

/**  * @mixin ..\Eloquent  */

namespace App\Models\OneC;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employees1cPost extends Model
{
    use DevExtremeDataSourceLoadable, SoftDeletes;

    protected $table = 'employees_1c_posts';

    protected $guarded = ['id'];
}
