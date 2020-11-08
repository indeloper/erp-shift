<?php

use Faker\Generator as Faker;

use App\Models\Manual\ManualMaterialCategory;

$factory->define(ManualMaterialCategory::class, function (Faker $faker) {
    $units = ['шт', 'т', 'м.п', 'м2', 'м3'];

    return [
        'description' => $faker->sentence(2, true),
        'category_unit' => $units[random_int(0, count($units) - 1)],
    ];
});
