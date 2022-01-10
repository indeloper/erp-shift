<?php

namespace Tests\Feature;

use App\Models\Manual\ManualMaterial;
use App\Models\Project;
use App\Models\ProjectObject;
use App\Models\User;
use App\Models\WorkVolume\WorkVolume;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    /** @test */
    public function it_filters_project_by_material(): void
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

    /** @test */
    public function project_getter_for_human_accounting_can_return_nothing(): void
    {
        // Given no projects
        Project::query()->delete();
        // Given user
        $user = factory(User::class)->create();

        // When user make post request with data
        $data = [];
        $response = $this->actingAs($user)->post(route('projects::get_projects_for_human'), $data)->json();

        // Then results should be empty
        $this->assertEmpty($response);
    }

    /** @test */
    public function project_getter_for_human_accounting_can_return_10_first_projects(): void
    {
        Project::query()->delete();
        // Given object
        $object = factory(ProjectObject::class)->create(['short_name' => 'NAMETAG']);
        // Given two projects collection
        $firstCollection = factory(Project::class, 10)->create(['object_id' => $object->id]);
        $secondCollection = factory(Project::class, 10)->create(['object_id' => $object->id]);
        // Given user
        $user = factory(User::class)->create();

        // When user make post request with data
        $data = [];
        $response = $this->actingAs($user)->post(route('projects::get_projects_for_human'), $data)->json();

        // Then results should contain project from first collection
        $this->assertNotEmpty($response);
        $this->assertCount(10, $response);
        $this->assertEquals($firstCollection->pluck('id'), collect($response)->pluck('code'));
        // With special codename
        $this->assertEquals($firstCollection->pluck('name_with_object'), collect($response)->pluck('label'));
    }

    /** @test */
    public function project_getter_for_human_accounting_can_return_10_first_projects_plus_selected_one(): void
    {
        Project::query()->delete();
        // Given object
        $object = factory(ProjectObject::class)->create(['short_name' => 'NAMETAG']);
        // Given projects
        $firstCollection = factory(Project::class, 10)->create(['object_id' => $object->id]);
        $project = factory(Project::class)->create();
        // Given user
        $user = factory(User::class)->create();

        // When user make post request with data
        $data = ['selected' => $project->id];
        $response = $this->actingAs($user)->post(route('projects::get_projects_for_human'), $data)->json();

        // Then results should contain project from first collection and selected project
        $this->assertNotEmpty($response);
        $this->assertCount(11, $response);
        $firstCollection = $firstCollection->prepend($project);
        $this->assertEquals($firstCollection->pluck('id'), collect($response)->pluck('code'));
        // With special codename
        $this->assertEquals($firstCollection->pluck('name_with_object'), collect($response)->pluck('label'));
    }

    /** @test */
    public function project_getter_for_human_accounting_can_filter_projects_by_name(): void
    {
        Project::query()->delete();
        // Given projects
        $firstCollection = factory(Project::class, 10)->create(['name' => 'AS ALWAYS ШПУНТ']);
        $project = factory(Project::class)->create(['name' => 'NEW FRESH NAME']);
        // Given user
        $user = factory(User::class)->create();

        // When user make post request with data
        $data = ['q' => 'NEW'];
        $response = $this->actingAs($user)->post(route('projects::get_projects_for_human'), $data)->json();

        // Then results should contain project
        $this->assertNotEmpty($response);
        $this->assertCount(1, $response);
        $this->assertEquals(collect([$project])->pluck('id'), collect($response)->pluck('code'));
        // With special codename
        $this->assertEquals(collect([$project])->pluck('name_with_object'), collect($response)->pluck('label'));
    }

    /** @test */
    public function project_getter_for_human_accounting_can_filter_projects_by_object(): void
    {
        Project::query()->delete();
        // Given object !!! In this case I'll use short name, but it also can search by name and address of object
        $object = factory(ProjectObject::class)->create(['short_name' => 'NAMETAG']);
        // Given projects
        $firstCollection = factory(Project::class, 10)->create(['object_id' => factory(ProjectObject::class)->create()->id]);
        $project = factory(Project::class)->create(['object_id' => $object->id]);
        // Given user
        $user = factory(User::class)->create();

        // When user make post request with data
        $data = ['q' => mb_substr($object->short_name, 0, 6)];
        $response = $this->actingAs($user)->post(route('projects::get_projects_for_human'), $data)->json();

        // Then results should contain project
        $this->assertNotEmpty($response);
        $this->assertCount(1, $response);
        $this->assertEquals(collect([$project])->pluck('id'), collect($response)->pluck('code'));
        // With special codename
        $this->assertEquals(collect([$project])->pluck('name_with_object'), collect($response)->pluck('label'));
    }
}
