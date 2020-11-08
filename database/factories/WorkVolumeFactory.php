<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Project;
use App\Models\User;
use App\Models\WorkVolume\WorkVolume;
use Faker\Generator as Faker;

$factory->define(WorkVolume::class, function (Faker $faker) {
    return [
        'user_id' => User::inRandomOrder()->first()->id ?? factory(User::class),
        'project_id' => Project::inRandomOrder()->first()->id ?? factory(Project::class),
        'type' => rand(0, 1),
    ];
});
