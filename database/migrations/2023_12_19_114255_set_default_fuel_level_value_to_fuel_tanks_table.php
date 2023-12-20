<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SetDefaultFuelLevelValueToFuelTanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $fuelLevelNullTanks = DB::table('fuel_tanks')->whereNull('fuel_level');
            $fuelLevelNullTanks->update(['fuel_level' => 0]);
            $table->integer('fuel_level')->nullable(false)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fuel_tanks', function (Blueprint $table) {
            //
        });
    }
}
