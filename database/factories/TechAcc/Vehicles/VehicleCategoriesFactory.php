<?php



namespace Database\Factories\TechAcc\Vehicles;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TechAcc\Vehicles\VehicleCategories;
use App\Models\User;

class VehicleCategoriesFactory extends Factory
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
            'name' => $this->faker->word,
            'description' => rand(0, 1) ? $this->faker->text : '',
        ];
    }
}
