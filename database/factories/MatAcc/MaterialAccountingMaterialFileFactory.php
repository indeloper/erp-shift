<?php

namespace Database\Factories\MatAcc;

use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialAccountingMaterialFileFactory extends Factory
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
            'operation_material_id' => in_array('operation_material_id', $passedAttributes) ?: MaterialAccountingOperationMaterials::factory()->create()->id,
            'file_name' => $this->faker->safeColorName(), // hehe
            'path' => 'storage/docs/mat_acc_operation_files',
            'type' => 1,
        ];
    }
}
