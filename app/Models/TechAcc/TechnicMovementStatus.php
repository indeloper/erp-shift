<?php

namespace App\Models\TechAcc;

use Illuminate\Database\Eloquent\Model;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\SoftDeletes;

class TechnicMovementStatus extends Model
{
    use DevExtremeDataSourceLoadable, SoftDeletes;
    
    protected $guarded = ['id'];
}
