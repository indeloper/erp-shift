<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\TechAcc\Defects\Defects;
use App\Models\TechAcc\OurTechnic;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Defects::class, function (Faker $faker) {
    return [
        'user_id' => User::inRandomOrder()->first()->id ?? factory(User::class)->create()->id,
        // right now here will be only technics
        'defectable_id' => OurTechnic::inRandomOrder()->first()->id ?? factory(OurTechnic::class)->create()->id,
        'defectable_type' => OurTechnic::class,
        'description' => $faker->paragraph,
        'status' => 1,
    ];
});
