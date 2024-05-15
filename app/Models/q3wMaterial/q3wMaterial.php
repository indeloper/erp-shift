<?php

/**  * @mixin ..\Eloquent  */

namespace App\Models\q3wMaterial;

use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class q3wMaterial extends Model
{
    use DevExtremeDataSourceLoadable, SoftDeletes;

    protected $guarded = ['id'];

    public function standard(): HasOne
    {
        return $this->hasOne(q3wMaterialStandard::class, 'standard_id', 'id');
    }
}
