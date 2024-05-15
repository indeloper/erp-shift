<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::rename('fuel_tank_transfer_hystories', 'fuel_tank_transfer_histories');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::rename('fuel_tank_transfer_histories', 'fuel_tank_transfer_hystories');
    }
};
