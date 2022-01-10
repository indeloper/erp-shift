<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\HumanResources\JobCategory;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(JobCategory::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'user_id' => User::inRandomOrder()->first()->id ?? factory(User::class)->create()->id,
    ];
});
