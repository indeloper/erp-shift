<?php
/**  * @mixin ..\Eloquent  */
namespace App\Models\q3wMaterial;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class q3wMaterialComment extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable;
}
