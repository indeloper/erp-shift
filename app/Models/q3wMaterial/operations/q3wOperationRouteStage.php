<?php
/**  * @mixin ..\Eloquent  */
namespace App\Models\q3wMaterial\operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class q3wOperationRouteStage extends Model
{
    use SoftDeletes;

    protected $guarded = array('id');
}

