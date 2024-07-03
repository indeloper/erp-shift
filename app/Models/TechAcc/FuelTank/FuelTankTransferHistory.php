<?php

namespace App\Models\TechAcc\FuelTank;

use App\Traits\DefaultSortable;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelTankTransferHistory extends Model
{
    use DefaultSortable, DevExtremeDataSourceLoadable, SoftDeletes;

    protected $guarded = ['id'];

    public $defaultSortOrder = [
        'id' => 'desc',
    ];
}
