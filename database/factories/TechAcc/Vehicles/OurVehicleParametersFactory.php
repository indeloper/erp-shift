<?php



namespace Database\Factories\TechAcc\Vehicles;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TechAcc\Vehicles\OurVehicleParameters;
use App\Models\TechAcc\Vehicles\OurVehicles;
use App\Models\TechAcc\Vehicles\VehicleCategoryCharacteristics;

class OurVehicleParametersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'characteristic_id' => VehicleCategoryCharacteristics::inRandomOrder()->first()->id ?? VehicleCategoryCharacteristics::factory()->create()->id,
            'vehicle_id' => OurVehicles::inRandomOrder()->first()->id ?? OurVehicles::factory()->create()->id,
            'value' => $this->faker->word,
        ];
    }
}
