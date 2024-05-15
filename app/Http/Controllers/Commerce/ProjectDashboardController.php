<?php

namespace App\Http\Controllers\Commerce;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest\ProjectStatRequest;
use App\Models\Project;
use App\Services\Commerce\ProjectDashboardService;
use Illuminate\Http\Request;

class ProjectDashboardController extends Controller
{
    public function importantProjects(Request $request): Response
    {
        $projects = Project::where('is_important', 1)->get();

        return response([
            'projects' => $projects,
        ]);
    }

    public function projectStats(ProjectStatRequest $request): Response
    {
        $project = Project::findOrFail($request->project_id);

        $data = (new ProjectDashboardService())->collectStats($project);

        return response([
            'data' => $data,
        ]);
    }
}
