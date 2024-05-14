<?php

namespace Tests\Feature\OldModules;

use App\Models\Project;
use App\Models\User;
use App\Models\WorkVolume\WorkVolume;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WorkWolumes extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /** @test */
    public function when_we_store_work_volume_with_tongue_name_that_already_exist_request_should_return_errors()
    {
        // Given user
        $user = User::inRandomOrder()->first() ?? factory(User::class)->create();
        // Given project
        $project = factory(Project::class)->create();
        // Given tongue work volume with name
        $name = 'FIRST';
        $work_volume = factory(WorkVolume::class)->create(['user_id' => $user->id, 'project_id' => $project->id, 'option' => $name, 'type' => 0]);

        // When we make post-request with data
        $data = [
            'add_tongue' => 1,
            'work_volume_tongue_id' => 'new',
            'option_tongue' => $name,
            'tongue_description' => $this->faker->sentence,
        ];
        $response = $this->actingAs($user)->post(route('projects::work_volume_request::store', $project->id), $data);

        // Then we must have errors in session
        $response->assertSessionHasErrors('duplicate_tongue');
    }

    /** @test */
    public function when_we_store_work_volume_with_pile_name_that_already_exist_request_should_return_errors()
    {
        // Given user
        $user = User::inRandomOrder()->first() ?? factory(User::class)->create();
        // Given project
        $project = factory(Project::class)->create();
        // Given pile work volume with name
        $name = 'FIRST';
        $work_volume = factory(WorkVolume::class)->create(['user_id' => $user->id, 'project_id' => $project->id, 'option' => $name, 'type' => 1]);

        // When we make post-request with data
        $data = [
            'add_pile' => 1,
            'work_volume_pile_id' => 'new',
            'option_pile' => $name,
            'pile_description' => $this->faker->sentence,
        ];
        $response = $this->actingAs($user)->post(route('projects::work_volume_request::store', $project->id), $data);

        // Then we must have errors in session
        $response->assertSessionHasErrors('duplicate_pile');
    }

    /** @test */
    public function when_we_store_work_volume_with_both_names_already_exist_request_should_return_errors()
    {
        // Given user
        $user = User::inRandomOrder()->first() ?? factory(User::class)->create();
        // Given project
        $project = factory(Project::class)->create();
        // Given tongue and pile work volumes with name
        $name = 'FIRST';
        $work_volume_tongue = factory(WorkVolume::class)->create(['user_id' => $user->id, 'project_id' => $project->id, 'option' => $name, 'type' => 0]);
        $work_volume_pile = factory(WorkVolume::class)->create(['user_id' => $user->id, 'project_id' => $project->id, 'option' => $name, 'type' => 1]);

        // When we make post-request with data
        $data = [
            'add_pile' => 1,
            'work_volume_pile_id' => 'new',
            'option_pile' => $name,
            'pile_description' => $this->faker->sentence,
            'add_tongue' => 1,
            'work_volume_tongue_id' => 'new',
            'option_tongue' => $name,
            'tongue_description' => $this->faker->sentence,
        ];
        $response = $this->actingAs($user)->post(route('projects::work_volume_request::store', $project->id), $data);

        // Then we must have errors in session
        $response->assertSessionHasErrors(['duplicate_pile', 'duplicate_tongue']);
    }
}
