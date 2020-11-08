<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;

use App\Models\ProjectObject;
use App\Models\TechAcc\CategoryCharacteristic;
use App\Models\TechAcc\OurTechnic;
use App\Models\TechAcc\TechnicCategory;
use Faker\Generator as Faker;

$factory->define(CategoryCharacteristic::class, function (Faker $faker) {
    return [
        'name' => $faker->words(3, true),
        'description' => $faker->sentence(),
    ];
});

$factory->define(OurTechnic::class, function (Faker $faker) {
    return [
        'brand' => $faker->word,
        'model' => $faker->word . $faker->randomNumber(3),
        'owner' => OurTechnic::$owners[array_rand(OurTechnic::$owners)],
        'start_location_id' => ProjectObject::count() ? ProjectObject::inRandomOrder()->first()->id : factory(ProjectObject::class)->create()->id,
        'technic_category_id' => TechnicCategory::count() ? TechnicCategory::inRandomOrder()->first()->id : factory(TechnicCategory::class)->create()->id,
        'exploitation_start' => \Carbon\Carbon::now()->subDays(60),
        'inventory_number' => $faker->randomNumber(5),
    ];
});
