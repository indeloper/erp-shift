<?php



namespace Database\Factories\TechAcc;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProjectObject;
use App\Models\TechAcc\CategoryCharacteristic;
use App\Models\TechAcc\OurTechnic;
use App\Models\TechAcc\TechnicCategory;

class CategoryCharacteristicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
        ];
    }
}
