<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use Faker\Generator as Faker;

$factory->define(FuelTank::class, function (Faker $faker) {
    if (! ProjectObject::count()) {
        factory(ProjectObject::class)->create();
    }

    return [
        'fuel_level' => $faker->randomFloat(3, 0, 100000),
        'tank_number' => (string) $faker->randomNumber(null, false),
        'object_id' => ProjectObject::first(),
        'explotation_start' => $faker->dateTimeBetween($startDate = '-2 years', $endDate = 'now', $timezone = null),
    ];
});
