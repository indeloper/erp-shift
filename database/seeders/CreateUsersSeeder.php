<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CreateUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        User::factory()->create(['department_id' => '1', 'group_id' => '3']);
        User::factory()->create(['department_id' => '10', 'group_id' => '5']);
        User::factory()->create(['department_id' => '9', 'group_id' => '9']);
        User::factory()->create(['department_id' => '6', 'group_id' => '7']);
        User::factory()->create(['department_id' => '9', 'group_id' => '16']);
        User::factory()->create(['department_id' => '10', 'group_id' => '19']);
        User::factory()->create(['department_id' => '8', 'group_id' => '17']);
        User::factory()->create(['department_id' => '6', 'group_id' => '36']);
        User::factory()->create(['department_id' => '6', 'group_id' => '35']);
        User::factory()->create(['department_id' => '10', 'group_id' => '33']);
        User::factory()->create(['department_id' => '10', 'group_id' => '34']);
    }
}
