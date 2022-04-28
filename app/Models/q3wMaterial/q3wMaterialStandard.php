<?php
namespace App\Models\q3wMaterial;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class q3wMaterialStandard extends Model
{

    use SoftDeletes, DevExtremeDataSourceLoadable;

    protected $appends = ['standard_properties', 'brands'];
    protected $guarded = array('id');

    public function materialType() {
        return $this->belongsTo(q3wMaterialType::class,'material_type','id');
    }

    public function getStandardPropertiesAttribute () {
        if (isset($this->standard_property_ids)) {
            return explode(',', $this->standard_property_ids);
        } else {
            return null;
        }
    }

    public function getBrandsAttribute () {
        if (isset($this->brand_ids)) {
            return explode(',', $this->brand_ids);
        } else {
            return null;
        }
    }
}
