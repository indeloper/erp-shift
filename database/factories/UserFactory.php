<?php

use Faker\Generator as Faker;

use App\Models\Department;
use App\Models\Group;
/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Models\User::class, function (Faker $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'person_phone' => random_int(100, 99999),
        'work_phone' => random_int(89000000000, 89999999999),
        'department_id' => Department::inRandomOrder()->first(),
        'group_id' => Group::inRandomOrder()->first(),
        'status' => 1,
        'is_su' => 0,
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),
    ];
});
