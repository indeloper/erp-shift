<?php

/** @var Factory $factory */

use App\Model;
use App\Models\ProjectObject;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(ProjectObject::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'address' => $faker->address
    ];
});
