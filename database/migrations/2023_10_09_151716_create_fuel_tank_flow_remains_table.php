<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fuel_tank_flow_remains', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('fuel_tank_id')->unsigned()->comment('ID топливной емкости');
            $table->foreign('fuel_tank_id')->references('id')->on('fuel_tanks');
            $table->float('volume', 8, 3)->comment('Остаток топлива');

            $table->timestamps();
        });

        DB::statement("ALTER TABLE fuel_tank_flow_remains COMMENT 'Регистр накопления - остатки топлива в емкостях'");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_tank_flow_remains');
    }
};
