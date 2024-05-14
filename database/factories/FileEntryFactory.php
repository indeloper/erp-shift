<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\FileEntry;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(FileEntry::class, function (Faker $faker) {
    return [
        'filename' => $faker->words(4, true),
        'size' => $faker->randomNumber(4),
        'mime' => $faker->mimeType,
        'original_filename' => $faker->word.$faker->fileExtension,
        'user_id' => User::first()->id,
    ];
});
