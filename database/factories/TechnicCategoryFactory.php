<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\Models\TechAcc\TechnicCategory;
use Faker\Generator as Faker;

$factory->define(TechnicCategory::class, function (Faker $faker) {
    return [
        'name' => $faker->words(2, true),
        'description' => $faker->sentence(),
    ];
});
