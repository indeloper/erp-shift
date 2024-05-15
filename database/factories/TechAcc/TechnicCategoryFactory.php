<?php



namespace Database\Factories\TechAcc;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TechAcc\TechnicCategory;

class TechnicCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
        ];
    }
}
