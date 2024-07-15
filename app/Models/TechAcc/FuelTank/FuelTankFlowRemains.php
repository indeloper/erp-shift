<?php

namespace App\Models\TechAcc\FuelTank;

use Illuminate\Database\Eloquent\Model;

class FuelTankFlowRemains extends Model
{
    protected $guarded = ['id'];

    public function fuelTank()
    {
        $this->belongsToMany(FuelTank::class);
    }
}
