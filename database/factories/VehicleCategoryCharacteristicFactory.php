<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\TechAcc\Vehicles\VehicleCategories;
use App\Models\TechAcc\Vehicles\VehicleCategoryCharacteristics;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(VehicleCategoryCharacteristics::class, function (Faker $faker) {
    return [
        'category_id' => VehicleCategories::inRandomOrder()->first()->id ?? factory(VehicleCategories::class)->create()->id,
        'name' => $faker->word,
        'short_name' => rand(0, 1) ? $faker->word : '',
        'unit' => rand(0, 1) ? $faker->word : '',
        'show' => rand(0, 1),
    ];
});
