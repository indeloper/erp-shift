<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Group;
use App\Models\ProjectObject;
use Carbon\Carbon;
use Faker\Generator as Faker;
use App\Models\MatAcc\MaterialAccountingOperation;

$factory->define(MaterialAccountingOperation::class, function (Faker $faker) {
    $rps = Group::with('users')->find([27, 13, 19])->pluck('users')->flatten();
    $responsible_user = $rps->random();

    return [
        'type' => 1,
        'object_id_from' => ProjectObject::first()->id,
        'object_id_to' => ProjectObject::first()->id,

        'planned_date_from' => Carbon::today()->format('d.m.Y'),
        'planned_date_to' => Carbon::today()->format('d.m.Y'),
        'actual_date_from' => Carbon::today()->format('d.m.Y'),
        'actual_date_to' => Carbon::today()->format('d.m.Y'),

        'comment_from' => $faker->text(20),
        'comment_to' => $faker->text(20),
        'comment_author' => $faker->text(20),

        'author_id' => $responsible_user,
        'sender_id' => $responsible_user,
        'recipient_id' => $responsible_user,
//        'supplier_id',
        'responsible_RP' => $responsible_user,

        'status' => 2,
        'is_close' => 0,

        'reason' => $faker->text(20),
    ];
});
