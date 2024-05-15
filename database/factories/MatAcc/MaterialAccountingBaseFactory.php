<?php

namespace Database\Factories\MatAcc;

use App\Models\Manual\ManualMaterial;
use App\Models\ProjectObject;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialAccountingBaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'object_id' => ProjectObject::factory()->create()->id,
            'manual_material_id' => ManualMaterial::factory()->create()->id,
            'date' => now()->format('d.m.Y'),
            'count' => $this->faker->numberBetween(0, 100),
            'unit' => 'т',
        ];
    }
}
