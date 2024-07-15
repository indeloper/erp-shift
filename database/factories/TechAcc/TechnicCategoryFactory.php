<?php

namespace Database\Factories\TechAcc;

use Illuminate\Database\Eloquent\Factories\Factory;

class TechnicCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
        ];
    }
}
