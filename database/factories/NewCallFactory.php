<?php

use App\Models\User;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Models\Task::class, function (Faker $faker) {
    $user = User::inRandomOrder()->where('work_phone', '!=', null)->first();

    return [
        'name' => 'Обработка входящего звонка',
        'incoming_phone' => rand(79000000000, 79999999999),
        'internal_phone' => $user->work_phone,
        'responsible_user_id' => $user->id,
        'status' => 2,
        'expired_at' => Carbon::now()->addHours(1),
    ];
});
