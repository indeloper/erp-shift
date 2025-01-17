<?php

namespace Database\Factories\CommercialOffer;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommercialOfferFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::inRandomOrder()->first()->id ?? Project::factory()->create()->id,
            'name' => $this->faker->word(),
            'file_name' => $this->faker->word(),
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory()->create()->id,
            'status' => 1, // in work
            'is_tongue' => rand(0, 1),
            'option' => 'По умолчанию',
            'version' => 1,
        ];
    }
}
