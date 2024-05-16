<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('fuel_tank_flow_types')->where('name', 'Поступление')->update(['name' => 'Приход']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
