<?php

namespace App\Traits;

use App\Models\Task;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Taskable
{
    /**
     * Morph relation to tasks
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Morph relation only for active tasks
     */
    public function active_tasks(): MorphMany
    {
        return $this->tasks()->where('is_solved', 0);
    }
}
