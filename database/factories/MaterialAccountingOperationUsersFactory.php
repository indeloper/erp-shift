<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationResponsibleUsers;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(MaterialAccountingOperationResponsibleUsers::class, function (Faker $faker) {
    $passedAttributes = func_get_arg(1);

    return [
        'operation_id' => function () use ($passedAttributes) {
            if (! in_array('operation_id', $passedAttributes)) {
                return factory(MaterialAccountingOperation::class)->create()->id;
            }
        },
        'user_id' => function () use ($passedAttributes) {
            if (! in_array('user_id', $passedAttributes)) {
                return factory(User::class)->create()->id;
            }
        },
    ];
});
