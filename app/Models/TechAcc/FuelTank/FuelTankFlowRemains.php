<?php

namespace App\Models\TechAcc\FuelTank;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelTankFlowRemains extends Model
{
    
    protected $guarded = ['id'];

    public function fuelTank()
    {
        $this->belongsToMany(FuelTank::class);
    }

}
