<?php

namespace Database\Factories\TechAcc\Vehicles;

use App\Models\TechAcc\Vehicles\OurVehicles;
use App\Models\TechAcc\Vehicles\VehicleCategoryCharacteristics;
use Illuminate\Database\Eloquent\Factories\Factory;

class OurVehicleParametersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'characteristic_id' => VehicleCategoryCharacteristics::inRandomOrder()->first()->id ?? VehicleCategoryCharacteristics::factory()->create()->id,
            'vehicle_id' => OurVehicles::inRandomOrder()->first()->id ?? OurVehicles::factory()->create()->id,
            'value' => $this->faker->word(),
        ];
    }
}
