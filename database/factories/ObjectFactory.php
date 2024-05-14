<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;

$factory->define(App\Models\ProjectObject::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'address' => $faker->address,
    ];
});
