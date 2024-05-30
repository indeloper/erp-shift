<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce\Project;

use App\Models\ProjectObject;
use Illuminate\Http\Request;

final class ProjectObjectController
{

    public function index(Request $request)
    {
        return ProjectObject::query()
            ->where('project_id', $request->get('project_id'))
            ->paginate(15);
    }

}