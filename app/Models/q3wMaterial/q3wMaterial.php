<?php
/**  * @mixin ..\Eloquent  */
namespace App\Models\q3wMaterial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DevExtremeDataSourceLoadable;

class q3wMaterial extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable;

    protected $guarded = array('id');

    public function standard()
    {
        return $this->hasOne(q3wMaterialStandard::class, 'standard_id', 'id');
    }
}
