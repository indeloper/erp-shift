<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Manual\ManualMaterial;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use Faker\Generator as Faker;

$factory->define(MaterialAccountingOperationMaterials::class, function (Faker $faker) {
    $passedAttributes = func_get_arg(1);

    return [
        'operation_id' => function () use ($passedAttributes) {
            if (! in_array('operation_id', $passedAttributes)) {
                return factory(MaterialAccountingOperation::class)->create()->id;
            }
        },
        'manual_material_id' => in_array('manual_material_id', $passedAttributes) ?: factory(ManualMaterial::class)->create()->id,
        'count' => $faker->numberBetween(0, 100),
        'unit' => 1,
        'type' => 3,
        'used' => 0,
    ];
});
