<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce\Project;

use App\Http\Resources\Project\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

final class ProjectController
{

    public function index()
    {
        return ProjectResource::collection(
            Project::query()
                ->with(['contractor'])
                ->latest()
                ->paginate(15)
        );
    }

    public function store(Request $request)
    {
        $project = Project::query()->forceCreateQuietly([
            'name'    => $request->data['name'],
            'address' => $request->data['address'],
        ]);

        return ProjectResource::make($project);
    }

    public function show($project_id)
    {
        return ProjectResource::make(
            Project::query()->findOrFail($project_id)
        );
    }

    public function update(Request $request, $project_id)
    {
        $project = Project::query()->findOrFail($project_id);

        $project->update([
            'name'    => $request['data']['name'],
            'address' => $request['data']['address'],
        ]);

        return ProjectResource::make($project);
    }

}