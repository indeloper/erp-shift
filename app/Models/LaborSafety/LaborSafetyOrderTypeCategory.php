<?php

/**  * @mixin ..\Eloquent  */

namespace App\Models\LaborSafety;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaborSafetyOrderTypeCategory extends Model
{
    use DevExtremeDataSourceLoadable, SoftDeletes;

    protected $guarded = ['id'];
}
