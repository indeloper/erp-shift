<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Task;
use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(Task::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'description' => $faker->text(50),
        'project_id' => null,
        'contractor_id' => null,
        'user_id' => null,
        'responsible_user_id' => null,
        'contact_id' => null,
        'incoming_phone' => null,
        'internal_phone' => null,
        'expired_at' => now()->addHours(8),
        'final_note' => null,
        'is_solved' => 0,
        'status' => 1,
        'is_seen' => 0,
    ];
});
