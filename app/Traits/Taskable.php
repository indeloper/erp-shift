<?php

namespace App\Traits;

use App\Models\Task;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Taskable
{
    /**
     * Morph relation to tasks
     *
     * @return MorphMany
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Morph relation only for active tasks
     *
     * @return MorphMany
     */
    public function active_tasks()
    {
        return $this->tasks()->where('is_solved', 0);
    }
}
