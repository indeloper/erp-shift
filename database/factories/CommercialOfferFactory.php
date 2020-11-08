<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CommercialOffer\CommercialOffer;
use App\Models\Project;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(CommercialOffer::class, function (Faker $faker) {
    return [
        'project_id' => Project::inRandomOrder()->first()->id ?? factory(Project::class)->create()->id,
        'name' => $faker->word,
        'file_name' => $faker->word,
        'user_id' => User::inRandomOrder()->first()->id ?? factory(User::class)->create()->id,
        'status' => 1, // in work
        'is_tongue' => rand(0, 1),
        'option' => 'По умолчанию',
        'version' => 1,
    ];
});
