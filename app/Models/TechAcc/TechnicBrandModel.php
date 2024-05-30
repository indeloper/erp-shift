<?php

namespace App\Models\TechAcc;

use App\Traits\DefaultSortable;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TechnicBrandModel extends Model
{
    use DefaultSortable, DevExtremeDataSourceLoadable, SoftDeletes;

    protected $guarded = ['id'];

    public $defaultSortOrder = [
        'name' => 'asc',
    ];
}
