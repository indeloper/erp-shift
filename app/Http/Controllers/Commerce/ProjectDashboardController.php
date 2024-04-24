<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest\ProjectStatRequest;
use App\Models\Project;
use App\Services\Commerce\ProjectDashboardService;
use Illuminate\Http\Request;

class ProjectDashboardController extends Controller
{
    public function importantProjects(Request $request)
    {
        $projects = Project::where('is_important', 1)->get();

        return response([
            'projects' => $projects,
        ]);
    }

    public function projectStats(ProjectStatRequest $request)
    {
        $project = Project::findOrFail($request->project_id);

        $data =(new ProjectDashboardService())->collectStats($project);

        return response([
            'data' => $data,
        ]);
    }
}
