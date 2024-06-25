<?php

declare(strict_types=1);

namespace App\Repositories\Project;

use App\Models\Project;

final class ProjectRepository
{

    public function getProjectById(int $id): ?Project
    {
        return Project::query()->find($id);
    }

}