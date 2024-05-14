<?php

use App\Models\Contractors\Contractor;
use App\Models\Contractors\ContractorContact;
use Faker\Generator as Faker;

$factory->define(ContractorContact::class, function (Faker $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->email,
        'phone_number' => random_int(89000000000, 89999999999),
        'position' => $faker->word,
        'contractor_id' => Contractor::inRandomOrder()->first()->id,
    ];
});
