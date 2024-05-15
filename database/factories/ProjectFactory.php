<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Contractors\Contractor;
use App\Models\Project;
use App\Models\ProjectObject;
use App\Models\User;

class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'contractor_id' => Contractor::inRandomOrder()->first()->id ?? Contractor::factory()->create()->id,
            'name' => $this->faker->word,
            'object_id' => ProjectObject::inRandomOrder()->first()->id ?? ProjectObject::factory()->create()->id,
            'description' => $this->faker->text(30),
            'is_important' => 0,
        ];
    }
}
