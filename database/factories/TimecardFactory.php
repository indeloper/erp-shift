<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\HumanResources\Timecard;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Timecard::class, function (Faker $faker) {
    $passedData = func_get_arg(1);
    return [
        'user_id' => function () use ($passedData) {
            if (! array_key_exists('user_id', $passedData)) {
                return factory(User::class)->create()->id;
            }
        },
        'author_id' => function () use ($passedData) {
            if (! array_key_exists('author_id', $passedData)) {
                return factory(User::class)->create()->id;
            }
        },
        'month' => $faker->month,
        'ktu' => 0,
        'is_opened' => 1,
    ];
});
