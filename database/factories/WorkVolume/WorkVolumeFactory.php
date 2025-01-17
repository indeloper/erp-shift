<?php

namespace Database\Factories\WorkVolume;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkVolumeFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'project_id' => Project::inRandomOrder()->first()->id ?? Project::factory(),
            'type' => rand(0, 1),
        ];
    }
}
