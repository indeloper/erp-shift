<?php

namespace App\models\q3wMaterial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class q3wMaterialSnapshotMaterial extends Model
{
    use SoftDeletes;

    protected $guarded = array('id');
}
