<?php

namespace Database\Factories;

use App\Models\Contractors\Contractor;
use App\Models\ProjectObject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'contractor_id' => Contractor::inRandomOrder()->first()->id ?? Contractor::factory()->create()->id,
            'name' => $this->faker->word(),
            'object_id' => ProjectObject::inRandomOrder()->first()->id ?? ProjectObject::factory()->create()->id,
            'description' => $this->faker->text(30),
            'is_important' => 0,
        ];
    }
}
