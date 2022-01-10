<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\HumanResources\Timecard;
use App\Models\HumanResources\TimecardDay;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(TimecardDay::class, function (Faker $faker) {
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
        'day' => $faker->dayOfMonth(now()),
    ];
});
