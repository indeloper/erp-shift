<?php

namespace Database\Factories\TechAcc\Vehicles;

use App\Models\TechAcc\Vehicles\VehicleCategories;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleCategoryCharacteristicsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'category_id' => VehicleCategories::inRandomOrder()->first()->id ?? VehicleCategories::factory()->create()->id,
            'name' => $this->faker->word(),
            'short_name' => rand(0, 1) ? $this->faker->word() : '',
            'unit' => rand(0, 1) ? $this->faker->word() : '',
            'show' => rand(0, 1),
        ];
    }
}
