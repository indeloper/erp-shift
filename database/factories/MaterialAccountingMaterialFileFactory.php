<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\MatAcc\MaterialAccountingMaterialFile;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use Faker\Generator as Faker;

$factory->define(MaterialAccountingMaterialFile::class, function (Faker $faker) {
    $passedAttributes = func_get_arg(1);

    return [
        'operation_id' => function () use ($passedAttributes) {
            if (! in_array('operation_id', $passedAttributes)) {
                return factory(MaterialAccountingOperation::class)->create()->id;
            }
        },
        'operation_material_id' => in_array('operation_material_id', $passedAttributes) ?: factory(MaterialAccountingOperationMaterials::class)->create()->id,
        'file_name' => $faker->safeColorName, // hehe
        'path' => 'storage/docs/mat_acc_operation_files',
        'type' => 1,
    ];
});
