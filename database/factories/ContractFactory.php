<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Contract\Contract;
use App\Models\Project;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Contract::class, function (Faker $faker) {
    $passedAttributes = func_get_arg(1);

    return [
        'project_id' => function () use ($passedAttributes) {
            if (! in_array('project_id', $passedAttributes)) {
                return factory(Project::class)->create()->id;
            }
        },
        'name' => $faker->colorName,
        'user_id' => function () use ($passedAttributes) {
            if (! in_array('user_id', $passedAttributes)) {
                return factory(User::class)->create()->id;
            }
        },
        'status' => 1,
        'version' => 1,
        'contract_id' => random_int(0, 100),
    ];
});
