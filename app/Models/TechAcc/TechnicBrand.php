<?php

namespace App\Models\TechAcc;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DevExtremeDataSourceLoadable;

class TechnicBrand extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable;

    protected $guarded = ['id'];
}
