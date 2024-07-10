<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'patronymic' => $this->faker->word(),
            'user_full_name' => $this->faker->userName(),
            'birthday' => $this->faker->word(),
            'email' => 'admin@admin.com',
            'person_phone' => $this->faker->phoneNumber(),
            'work_phone' => $this->faker->phoneNumber(),
            'department_id' => $this->faker->randomNumber(),
            'company' => $this->faker->company(),
            'job_category_id' => $this->faker->randomNumber(),
            'brigade_id' => $this->faker->randomNumber(),
            'image' => $this->faker->word(),
            'password' => bcrypt('password'),
            'status' => $this->faker->randomNumber(),
            'is_su' => 1,
            'remember_token' => Str::random(10),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'chat_id' => $this->faker->word(),
            'in_vacation' => false,
            'is_deleted' => false,
            'INN' => $this->faker->word(),
            'gender' => $this->faker->word(),

            'group_id' => Group::factory(),
        ];
    }
}
