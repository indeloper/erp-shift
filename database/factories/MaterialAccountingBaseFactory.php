<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Manual\ManualMaterial;
use App\Models\MatAcc\MaterialAccountingBase;
use App\Models\ProjectObject;
use Faker\Generator as Faker;

$factory->define(MaterialAccountingBase::class, function (Faker $faker) {
    return [
        'object_id' => factory(ProjectObject::class)->create()->id,
        'manual_material_id' => factory(ManualMaterial::class)->create()->id,
        'date' => now()->format('d.m.Y'),
        'count' => $faker->numberBetween(0, 100),
        'unit' => 'т',
    ];
});
