<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fuel_tank_transfer_hystories', function (Blueprint $table) {
            $table->boolean('tank_moving_confirmation')->nullable()->after('fuel_tank_id')->comment('Подтвержждение перемещения и передачи ответственности');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_tank_transfer_hystories', function (Blueprint $table) {
            $table->dropColumn('tank_moving_confirmation');
        });
    }
};
