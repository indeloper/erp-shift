<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProjectObject;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

final class ProjectObjectEventService
{

    public function getEvents(ProjectObject $projectObject): Collection
    {
        return Task::orderBy('created_at', 'desc')
            ->with('responsible_user', 'author', 'user')
            ->where('tasks.project_id', $projectObject->project_id)
            ->leftJoin('users', 'users.id', '=', 'tasks.responsible_user_id')
            ->leftjoin('projects', 'projects.id', 'tasks.project_id')
            ->leftjoin('contractors', 'contractors.id', 'tasks.contractor_id')
            ->leftjoin('work_volumes', 'tasks.target_id', 'work_volumes.id')
            ->select('users.last_name', 'users.first_name', 'users.patronymic',
                'projects.name as project_name',
                'contractors.short_name as contractor_name',
                'work_volumes.type', 'work_volumes.id as work_volume_id',
                'tasks.*')
            ->take(6)
            ->get();
    }

}