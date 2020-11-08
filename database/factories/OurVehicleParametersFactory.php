<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\TechAcc\Vehicles\OurVehicleParameters;
use App\Models\TechAcc\Vehicles\OurVehicles;
use App\Models\TechAcc\Vehicles\VehicleCategoryCharacteristics;
use Faker\Generator as Faker;

$factory->define(OurVehicleParameters::class, function (Faker $faker) {
    return [
        'characteristic_id' => VehicleCategoryCharacteristics::inRandomOrder()->first()->id ?? factory(VehicleCategoryCharacteristics::class)->create()->id,
        'vehicle_id' => OurVehicles::inRandomOrder()->first()->id ?? factory(OurVehicles::class)->create()->id,
        'value' => $faker->word,
    ];
});
