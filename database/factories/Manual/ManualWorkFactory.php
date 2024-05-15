<?php

namespace Database\Factories\Manual;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Manual\ManualWork;

class ManualWorkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $units = ['шт', 'т', 'м.п', 'м2', 'м3'];
        $nds = [0, 10, 20];

        return [
            'work_group_id' => random_int(1, 4),
            'name' => $this->faker->sentence(2, true),
            'description' => $this->faker->sentence(2, true),
            'price_per_unit' => random_int(10, 500),
            'unit' => $units[random_int(0, count($units) - 1)],
            'unit_per_days' => rand(1, 5),
            'nds' => $nds[random_int(0, 2)],
        ];
    }
}
