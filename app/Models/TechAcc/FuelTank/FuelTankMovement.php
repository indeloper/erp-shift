<?php

namespace App\Models\TechAcc\FuelTank;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Logable;
use App\Traits\DevExtremeDataSourceLoadable;

use Illuminate\Database\Eloquent\Model;

class FuelTankMovement extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable, Logable;

    protected $guarded = ['id'];
}
