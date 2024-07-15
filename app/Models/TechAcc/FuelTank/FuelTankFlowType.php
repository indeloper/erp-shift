<?php

namespace App\Models\TechAcc\FuelTank;

use App\Traits\DefaultSortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelTankFlowType extends Model
{
    use DefaultSortable, SoftDeletes;

    protected $guarded = ['id'];

    public $defaultSortOrder = [
        'id' => 'asc',
    ];
}
