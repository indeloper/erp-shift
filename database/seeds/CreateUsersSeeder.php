<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class CreateUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(User::class)->create(['department_id' => '1', 'group_id' => '3']);
        factory(User::class)->create(['department_id' => '10', 'group_id' => '5']);
        factory(User::class)->create(['department_id' => '9', 'group_id' => '9']);
        factory(User::class)->create(['department_id' => '6', 'group_id' => '7']);
        factory(User::class)->create(['department_id' => '9', 'group_id' => '16']);
        factory(User::class)->create(['department_id' => '10', 'group_id' => '19']);
        factory(User::class)->create(['department_id' => '8', 'group_id' => '17']);
        factory(User::class)->create(['department_id' => '6', 'group_id' => '36']);
        factory(User::class)->create(['department_id' => '6', 'group_id' => '35']);
        factory(User::class)->create(['department_id' => '10', 'group_id' => '33']);
        factory(User::class)->create(['department_id' => '10', 'group_id' => '34']);
    }
}
