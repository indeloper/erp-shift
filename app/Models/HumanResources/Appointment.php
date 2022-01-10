<?php

namespace App\Models\HumanResources;

use App\Traits\Logable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes, Logable;

    protected $fillable = ['appointmentable_id', 'appointmentable_type', 'project_id'];
}
