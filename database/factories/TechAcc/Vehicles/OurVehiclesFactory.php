<?php

namespace Database\Factories\TechAcc\Vehicles;

use App\Models\TechAcc\Vehicles\OurVehicles;
use App\Models\TechAcc\Vehicles\VehicleCategories;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OurVehiclesFactory extends Factory
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
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory()->create()->id,
            'number' => $this->faker->randomNumber(9),
            'trailer_number' => rand(0, 1) ? $this->faker->randomNumber(9) : '',
            'mark' => $this->faker->word(),
            'model' => $this->faker->word(),
            'owner' => OurVehicles::OWNERS[rand(1, 5)],
        ];
    }
}
