<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProjectObject;
use App\Models\WorkVolume\WorkVolume;
use Illuminate\Database\Eloquent\Collection;

final class ProjectObjectWorkVolumesService
{

    public function getWorkValues(ProjectObject $projectObject): Collection
    {
        return WorkVolume::where('project_id', $projectObject->project_id)
            ->where('type', '!=', 2)
            ->orderBy('work_volumes.version', 'desc')
            ->with('get_requests')
            ->get();
    }

}