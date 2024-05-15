<?php

namespace Database\Factories\Manual;

use App\Models\Manual\ManualMaterialCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManualMaterialCategoryAttributeFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $units = ['шт', 'т', 'м.п', 'м2', 'м3'];
        $names = ['Глубина', 'Ширина', 'Высота', 'Вес', 'Плотность', 'Объём', 'Площадь', 'Максимальный габарит', 'Глубина погружения', 'Длина', 'Радиус'];

        return [
            'name' => $names[random_int(0, count($names) - 1)],
            'description' => $this->faker->sentence(2, true),
            'unit' => $units[random_int(0, count($units) - 1)],
            'is_required' => random_int(0, 1),
            'category_id' => ManualMaterialCategory::inRandomOrder()->first()->id,
            'is_preset' => 0,
        ];
    }
}
