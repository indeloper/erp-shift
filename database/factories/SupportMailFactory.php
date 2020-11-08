<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\SupportMail;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(SupportMail::class, function (Faker $faker) {
    return [
        'title' => $faker->word,
        'description' => $faker->sentence,
        'user_id' => factory(User::class)->create()->id,
        'page_path' => $faker->url,
        'status' => 'new',
    ];
});
