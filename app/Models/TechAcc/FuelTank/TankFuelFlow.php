<?php

namespace App\Models\TechAcc\FuelTank;

use App\Traits\DevExtremeDataSourceLoadable;
use App\Traits\Logable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TankFuelFlow extends Model
{
    use DevExtremeDataSourceLoadable, Logable, SoftDeletes;

    protected $guarded = ['id'];
}
