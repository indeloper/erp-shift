<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectResponsibleUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $passedAttributes = func_get_arg(1);

        return [
            'project_id' => function () use ($passedAttributes) {
                if (! in_array('project_id', $passedAttributes)) {
                    return Project::factory()->create()->id;
                }
            },
            'user_id' => function () use ($passedAttributes) {
                if (! in_array('user_id', $passedAttributes)) {
                    return User::factory()->create()->id;
                }
            },
            'role' => 1,
        ];
    }
}
