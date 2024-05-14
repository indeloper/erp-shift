<?php

use App\Models\Contractors\Contractor;
use Faker\Generator as Faker;

$factory->define(Contractor::class, function (Faker $faker) {
    return [
        'full_name' => $faker->company,
        'short_name' => $faker->company,
        'inn' => random_int(89000000000, 89999999999),
        'legal_address' => $faker->address,
        'phone_number' => $faker->phoneNumber,
        'main_type' => null,
    ];
});
