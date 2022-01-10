<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Models\HumanResources\{Timecard, TimecardAddition};
use Faker\Generator as Faker;

$factory->define(TimecardAddition::class, function (Faker $faker) {
    $passedData = func_get_arg(1);
    return [
        'timecard_id' => function () use ($passedData) {
            if (! array_key_exists('timecard_id', $passedData)) {
                return factory(Timecard::class)->create()->id;
            }
        },
        'user_id' => function () use ($passedData) {
            if (! array_key_exists('user_id', $passedData)) {
                return factory(User::class)->create()->id;
            }
        },
        'type' => random_int(1, 3),
        'name' => $faker->colorName,
        'amount' => round(random_int(1, 100), 2)
    ];
});
