<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskChangingField extends Model
{
    protected $fillable = ['task_id', 'field_name', 'value', 'old_value'];
}
