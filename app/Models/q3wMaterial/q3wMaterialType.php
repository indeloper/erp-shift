<?php

namespace App\Models\q3wMaterial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class q3wMaterialType extends Model
{
    use SoftDeletes;

    protected $guarded = array('id');

    public function measureUnits() {
        return $this->hasOne(q3wMeasureUnit::class, 'measureUnit', 'id');
    }
}
