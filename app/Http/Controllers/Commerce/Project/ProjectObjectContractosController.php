<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce\Project;

use App\Http\Resources\ProjectObjectContractorResource;
use App\Models\ProjectObject;
use App\Models\ProjectObjectContractor;

final class ProjectObjectContractosController
{

    public function index(ProjectObject $projectObject)
    {
        return ProjectObjectContractorResource::collection(
            ProjectObjectContractor::query()
                ->with(['user', 'contractor'])
                ->where('project_object_id', $projectObject->id)
                ->paginate(15)
        );
    }

}