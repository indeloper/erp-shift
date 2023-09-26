<?php
namespace App\Models\q3wMaterial;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class q3wMaterialStandard extends Model
{

    use SoftDeletes, DevExtremeDataSourceLoadable;

    protected $guarded = array('id');

    public function materialType() {
        return $this->belongsTo(q3wMaterialType::class,'material_type','id');
    }

}
