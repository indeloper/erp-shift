<?php

/**  * @mixin ..\Eloquent  */

namespace App\Models\q3wMaterial;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class q3wMaterialSnapshotMaterialComment extends Model
{
    use DevExtremeDataSourceLoadable, SoftDeletes;

    protected $guarded = ['id'];
}
