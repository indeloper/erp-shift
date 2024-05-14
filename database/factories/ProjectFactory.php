<?php

use App\Models\Contractors\Contractor;
use App\Models\Project;
use App\Models\ProjectObject;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Project::class, function (Faker $faker) {
    return [
        'user_id' => User::inRandomOrder()->first()->id,
        'contractor_id' => Contractor::inRandomOrder()->first()->id ?? factory(Contractor::class)->create()->id,
        'name' => $faker->word,
        'object_id' => ProjectObject::inRandomOrder()->first()->id ?? factory(ProjectObject::class)->create()->id,
        'description' => $faker->text(30),
        'is_important' => 0,
    ];
});
