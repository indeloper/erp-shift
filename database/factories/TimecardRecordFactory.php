<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\HumanResources\TimecardDay;
use App\Models\HumanResources\TimecardRecord;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(TimecardRecord::class, function (Faker $faker) {
    $passedData = func_get_arg(1);
    return [
        'timecard_day_id' => function () use ($passedData) {
            if (! array_key_exists('timecard_day_id', $passedData)) {
                return factory(TimecardDay::class)->create()->id;
            }
        },
        'user_id' => function () use ($passedData) {
            if (! array_key_exists('user_id', $passedData)) {
                return factory(User::class)->create()->id;
            }
        },
        'type' => random_int(1, 3),
    ];
});

$factory->state(TimecardRecord::class, 'deal', function(Faker $faker) {
    return [
        'type' => TimecardRecord::TYPES_ENG['deals'],
        'tariff_id' => $faker->numberBetween(8, 12),
        'length' => $faker->numberBetween(1, 26),
        'amount' => $faker->numberBetween(4, 40),
    ];
});

$factory->state(TimecardRecord::class, 'working_hours', function(Faker $faker) {
    return [
        'type' => TimecardRecord::TYPES_ENG['working hours'],
        'tariff_id' => $faker->numberBetween(1, 7),
        'amount' => $faker->numberBetween(4, 40),
    ];
});

$factory->state(TimecardRecord::class, 'time_periods', function(Faker $faker) {
    $project_id = Project::count() > 0 ? Project::inRandomOrder()->first()->id : 1;
    $start = Carbon::now()->subHours($faker->numberBetween(0, 6));
    return [
        'type' => TimecardRecord::TYPES_ENG['time periods'],
        'project_id' => $project_id,
        'start' => $start->format('h:i'),
        'end' => $start->addHours($faker->numberBetween(0, 6))->format('h:i'),
    ];
});
