<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\HumanResources\Brigade;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Brigade::class, function (Faker $faker) {
    return [
        'number' => $faker->randomNumber(),
        'direction' => mt_rand(1, 3),
        'user_id' => factory(User::class)->create(),
    ];
});
