<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\TechAcc\Vehicles\VehicleCategories;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(VehicleCategories::class, function (Faker $faker) {
    return [
        'user_id' => User::inRandomOrder()->first()->id ?? factory(User::class)->create()->id,
        'name' => $faker->word,
        'description' => rand(0,1) ? $faker->text : '',
    ];
});
