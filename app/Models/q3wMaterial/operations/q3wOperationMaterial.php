<?php

/**  * @mixin ..\Eloquent  */

namespace App\Models\q3wMaterial\operations;

use App\Models\q3wMaterial\q3wMaterialStandard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class q3wOperationMaterial extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function standard(): HasOne
    {
        return $this->hasOne(q3wMaterialStandard::class, 'id', 'standard_id');
    }
}
