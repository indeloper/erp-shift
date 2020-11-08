<?php

use Faker\Generator as Faker;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectResponsibleUser;

$factory->define(ProjectResponsibleUser::class, function (Faker $faker) {
    $passedAttributes = func_get_arg(1);
    return [
        'project_id' => function () use ($passedAttributes) {
            if (! in_array('project_id', $passedAttributes)) {
                return factory(Project::class)->create()->id;
            }
        },
        'user_id' => function () use ($passedAttributes) {
            if (! in_array('user_id', $passedAttributes)) {
                return factory(User::class)->create()->id;
            }
        },
        'role' => 1
    ];
});
