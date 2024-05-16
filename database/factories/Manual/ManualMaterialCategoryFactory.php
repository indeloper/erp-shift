<?php

namespace Database\Factories\Manual;

use Illuminate\Database\Eloquent\Factories\Factory;

class ManualMaterialCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $units = ['шт', 'т', 'м.п', 'м2', 'м3'];

        return [
            'description' => $this->faker->sentence(2, true),
            'category_unit' => $units[random_int(0, count($units) - 1)],
        ];
    }
}
