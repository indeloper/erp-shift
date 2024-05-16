<?php

namespace Database\Factories\TechAcc\Defects;

use App\Models\TechAcc\OurTechnic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DefectsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory()->create()->id,
            // right now here will be only technics
            'defectable_id' => OurTechnic::inRandomOrder()->first()->id ?? OurTechnic::factory()->create()->id,
            'defectable_type' => OurTechnic::class,
            'description' => $this->faker->paragraph(),
            'status' => 1,
        ];
    }
}
