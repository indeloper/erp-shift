<?php

use Faker\Generator as Faker;

use App\Models\Manual\ManualWork;

$factory->define(ManualWork::class, function (Faker $faker) {
    $units = ['шт', 'т', 'м.п', 'м2', 'м3'];
    $nds = [0, 10, 20];
    return [
        'work_group_id' => random_int(1,4),
        'name' => $faker->sentence(2, true),
        'description' => $faker->sentence(2, true),
        'price_per_unit' => random_int(10, 500),
        'unit' => $units[random_int(0, count($units) - 1)],
        'unit_per_days' => rand(1, 5),
        'nds' => $nds[random_int(0, 2)]
    ];
});
