<?php

namespace Database\Factories\TechAcc\FuelTank;

use App\Models\ProjectObject;
use Illuminate\Database\Eloquent\Factories\Factory;

class FuelTankFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        if (! ProjectObject::count()) {
            ProjectObject::factory()->create();
        }

        return [
            'fuel_level' => $this->faker->randomFloat(3, 0, 100000),
            'tank_number' => (string) $this->faker->randomNumber(null, false),
            'object_id' => ProjectObject::first(),
            'explotation_start' => $this->faker->dateTimeBetween($startDate = '-2 years', $endDate = 'now', $timezone = null),
        ];
    }
}
