<?php

namespace App\Models\TechAcc;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DevExtremeDataSourceLoadable;
use App\Traits\DefaultSortable;

class TechnicBrand extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable, DefaultSortable;

    protected $guarded = ['id'];

    public $defaultSortOrder = [
        'name' => 'asc'
    ];

}
