<?php

namespace App\Models\TechAcc\FuelTank;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelTankTransferHystory extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];
}
