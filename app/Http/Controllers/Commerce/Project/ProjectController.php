<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce\Project;

use App\Models\Project;

final class ProjectController
{

    public function index()
    {
        return Project::query()
            ->paginate(15);
    }

}