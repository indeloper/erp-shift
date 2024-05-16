<?php

namespace Database\Factories\Manual;

use App\Models\Manual\ManualMaterialCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManualMaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $names = ['Камень', 'Кирпич', 'Арматура', 'Шпунт', 'Свая', 'Крепёж', 'Электрод', 'Гайка', 'Болт', 'Гайка', 'Проволока'];

        return [
            'name' => $names[random_int(0, count($names) - 1)],
            'description' => $this->faker->sentence(2, true),
            'category_id' => ManualMaterialCategory::inRandomOrder()->first()->id,
            'buy_cost' => random_int(50, 500),
            'use_cost' => random_int(50, 500),
        ];
    }
}
