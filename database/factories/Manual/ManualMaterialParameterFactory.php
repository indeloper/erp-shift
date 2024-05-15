<?php

namespace Database\Factories\Manual;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualMaterialParameter;

class ManualMaterialParameterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $mat = ManualMaterial::inRandomOrder()->first();

        return [
            'attr_id' => ManualMaterialCategory::find($mat->category_id)->attributes()->inRandomOrder()->first()->id,
            'mat_id' => $mat->id,
            'value' => random_int(1, 50),
        ];
    }
}
