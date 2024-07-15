<?php

namespace Database\Factories\MatAcc;

use App\Models\Manual\ManualMaterial;
use App\Models\MatAcc\MaterialAccountingOperation;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialAccountingOperationMaterialsFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $passedAttributes = func_get_arg(1);

        return [
            'operation_id' => function () use ($passedAttributes) {
                if (! in_array('operation_id', $passedAttributes)) {
                    return MaterialAccountingOperation::factory()->create()->id;
                }
            },
            'manual_material_id' => in_array('manual_material_id', $passedAttributes) ?: ManualMaterial::factory()->create()->id,
            'count' => $this->faker->numberBetween(0, 100),
            'unit' => 1,
            'type' => 3,
            'used' => 0,
        ];
    }
}
