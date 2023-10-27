<?php

namespace App\Models\TechAcc\FuelTank;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DevExtremeDataSourceLoadable;

class FuelTankTransferHystory extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable;
    protected $guarded = ['id'];
}
