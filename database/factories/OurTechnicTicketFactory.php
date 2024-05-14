<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ProjectObject;
use App\Models\TechAcc\OurTechnic;
use App\Models\TechAcc\OurTechnicTicket;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(OurTechnicTicket::class, function (Faker $faker, $attributes) {
    if (! ProjectObject::count()) {
        factory(ProjectObject::class, 6)->create();
    }
    if (! OurTechnic::count()) {
        $technic_id = factory(OurTechnic::class)->create();
    } else {
        $technic_id = OurTechnic::first()->id;
    }

    $sending_from_date = isset($attributes['sending_from_date']) ? Carbon::parse($attributes['sending_from_date']) : Carbon::now();
    $sending_to_date = isset($attributes['sending_to_date']) ? Carbon::parse($attributes['sending_to_date']) : $sending_from_date->addDays(2);
    $getting_from_date = isset($attributes['getting_from_date']) ? Carbon::parse($attributes['getting_from_date']) : $sending_to_date->addDays(2);
    $getting_to_date = isset($attributes['getting_to_date']) ? Carbon::parse($attributes['getting_to_date']) : $getting_from_date->addDays(3);
    $usage_from_date = isset($attributes['usage_from_date']) ? Carbon::parse($attributes['usage_from_date']) : $getting_to_date;
    $usage_to_date = isset($attributes['usage_to_date']) ? Carbon::parse($attributes['usage_to_date']) : $usage_from_date->addWeek();

    return [
        'our_technic_id' => $technic_id,
        'sending_object_id' => ProjectObject::first(),
        'getting_object_id' => ProjectObject::take(5)->get()->random(),
        'usage_days' => $faker->randomNumber(2),
        'sending_from_date' => $sending_from_date,
        'sending_to_date' => $sending_to_date,
        'getting_from_date' => $getting_from_date,
        'getting_to_date' => $getting_to_date,
        'usage_from_date' => $usage_from_date,
        'usage_to_date' => $usage_to_date,
        'comment' => $faker->text(150),
        'status' => 1,
        'type' => 3,
    ];
});
