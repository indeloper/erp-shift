<?php

namespace App\Models\TechAcc;

use App\Traits\DefaultSortable;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\SoftDeletes;

class TechnicMovementStatus extends Model
{
    use DevExtremeDataSourceLoadable, SoftDeletes, DefaultSortable;
    
    protected $guarded = ['id'];

    public $defaultSortOrder = [
        'sortOrder' => 'asc'
    ];
}
