<?php

namespace Tests\Feature;

use App\Models\Manual\ManualMaterial;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkVolume\WorkVolume;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    /** @test */
    public function it_filters_project_by_material()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::find(1));

        $wv = WorkVolume::where('project_id', 144)->first();
        $mat = $wv->materials()->first();
        $materials = ManualMaterial::inRandomOrder()->limit(3)->get()->push($mat->manual);
        $projects = collect($this->get(route('projects::index',  ['material_ids' => implode(',', $materials->pluck('id')->toArray())]))->assertOk()->viewData('projects'));

        $allProjects = Project::all();
        $allProjects = $allProjects->filter(function($project) use ($materials) {
            return $project->work_volumes()->whereHas('materials', function($mat) use($materials) {
                return $mat->whereIn('manual_material_id', $materials->pluck('id'));
            })->exists();
        });

        $this->assertEquals($allProjects->count(), $projects['total']);
    }
}
