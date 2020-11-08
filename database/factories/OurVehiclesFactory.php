<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\TechAcc\Vehicles\OurVehicles;
use App\Models\TechAcc\Vehicles\VehicleCategories;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(OurVehicles::class, function (Faker $faker) {
    return [
        'category_id' => VehicleCategories::inRandomOrder()->first()->id ?? factory(VehicleCategories::class)->create()->id,
        'user_id' => User::inRandomOrder()->first()->id ?? factory(User::class)->create()->id,
        'number' => $faker->randomNumber(9),
        'trailer_number' => rand(0, 1) ? $faker->randomNumber(9) : '',
        'mark' => $faker->word,
        'model' => $faker->word,
        'owner' => OurVehicles::OWNERS[rand(1,5)],
    ];
});
