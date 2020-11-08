<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Services\Commerce\ProjectDashboardService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectDashboardTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testSeeDashboardStats()
    {
        $project = Project::find(291);

        dd(json_decode(json_encode((new ProjectDashboardService())->collectStats($project)), true));
    }
}
