<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('fuel_tank_operations', function (Blueprint $table) {
            $table->float('result_value', 13, 3)->nullable();
            $table->float('value', 13, 3)->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('fuel_tank_operations', function (Blueprint $table) {
            $table->dropColumn('result_value');
            $table->float('value', 8, 3)->change();
        });
    }
};
