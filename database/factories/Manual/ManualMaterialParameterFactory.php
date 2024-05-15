<?php

namespace Database\Factories\Manual;

use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManualMaterialParameterFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $mat = ManualMaterial::inRandomOrder()->first();

        return [
            'attr_id' => ManualMaterialCategory::find($mat->category_id)->attributes()->inRandomOrder()->first()->id,
            'mat_id' => $mat->id,
            'value' => random_int(1, 50),
        ];
    }
}
