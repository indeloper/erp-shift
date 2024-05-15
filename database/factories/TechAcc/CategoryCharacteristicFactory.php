<?php

namespace Database\Factories\TechAcc;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryCharacteristicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
        ];
    }
}
