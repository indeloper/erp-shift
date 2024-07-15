<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskRedirect extends Model
{
    protected $fillable = ['task_id', 'user_id', 'responsible_user_id', 'redirect_note'];

    public static function tasks($task_ids)
    {
        $tasks = Task::whereIn('id', $task_ids)->where('is_solved', 0)->get();

        return $tasks;
    }
}
