<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskFile extends Model
{
    protected $fillable = ['project_id', 'user_id', 'file_name', 'original_name'];
}
