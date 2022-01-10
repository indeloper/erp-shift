<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\HumanResources\TariffRates;
use App\Models\HumanResources\JobCategory;
use App\Models\HumanResources\JobCategoryTariff;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(JobCategoryTariff::class, function (Faker $faker) {
    return [
        'job_category_id' => factory(JobCategory::class)->create()->id,
        'tariff_id' => TariffRates::inRandomOrder()->first()->id,
        'rate' => $faker->numberBetween(0, 10),
        'user_id' => User::inRandomOrder()->first()->id ?? factory(User::class)->create()->id,
    ];
});
