<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\HumanResources\PayAndHold;
use Faker\Generator as Faker;

$factory->define(PayAndHold::class, function (Faker $faker) {
    return [
        'name' => $faker->words(5, true),
        'short_name' => $faker->words(5, true),
        'type' => $faker->numberBetween(1, 2),
    ];
});
