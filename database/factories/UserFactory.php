<?php

namespace Database\Factories;

use Illuminate\Support\Facades\Hash;
use App\Models\Department;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected static ?string $password;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'person_phone' => random_int(100, 99999),
            'work_phone' => random_int(89000000000, 89999999999),
            'department_id' => Department::inRandomOrder()->first(),
            'group_id' => Group::inRandomOrder()->first(),
            'status' => 1,
            'is_su' => 0,
            'email' => $this->faker->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('secret'),
            'remember_token' => Str::random(10),
        ];
    }
}
