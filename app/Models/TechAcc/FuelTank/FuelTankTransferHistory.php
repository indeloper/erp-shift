<?php

namespace App\Models\TechAcc\FuelTank;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DevExtremeDataSourceLoadable;
use App\Traits\DefaultSortable;


class FuelTankTransferHistory extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable, DefaultSortable;
    protected $guarded = ['id'];

    public $defaultSortOrder = [
        'id' => 'desc'
    ];

}
