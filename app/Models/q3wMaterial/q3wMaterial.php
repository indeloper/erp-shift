<?php
/**  * @mixin ..\Eloquent  */
namespace App\models\q3wMaterial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class q3wMaterial extends Model
{
    use SoftDeletes;

    protected $guarded = array('id');

    public function standard()
    {
        return $this->hasOne(q3wMaterialStandard::class);
    }
}
