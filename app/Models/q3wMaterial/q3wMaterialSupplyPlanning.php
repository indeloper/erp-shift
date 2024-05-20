<?php

/**  * @mixin ..\Eloquent  */

namespace App\Models\q3wMaterial;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class q3wMaterialSupplyPlanning extends Model
{
    use DevExtremeDataSourceLoadable, SoftDeletes;

    protected $table = 'q3w_material_supply_planning';

    protected $guarded = ['id'];
}
