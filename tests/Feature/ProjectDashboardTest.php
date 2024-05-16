<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Services\Commerce\ProjectDashboardService;
use Tests\TestCase;

class ProjectDashboardTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testSeeDashboardStats(): void
    {
        $project = Project::find(291);

        dd(json_decode(json_encode((new ProjectDashboardService())->collectStats($project)), true));
    }
}
