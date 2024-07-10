<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call(CreateUsersSeeder::class);
        // $this->call(DefaultManual::class);
        // $this->call('CleanMaterialAccountingData');

        User::factory(1)->create();
    }
}
