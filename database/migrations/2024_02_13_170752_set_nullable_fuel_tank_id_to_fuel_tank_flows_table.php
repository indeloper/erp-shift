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
    public function up()
    {
        Schema::table('fuel_tank_flows', function (Blueprint $table) {
            $table->dropForeign(['fuel_tank_id']);
        });
        Schema::table('fuel_tank_flows', function (Blueprint $table) {
            $table->bigInteger('fuel_tank_id')->nullable()->unsigned()->comment('ID топливной емкости')->change();
            $table->foreign('fuel_tank_id')->references('id')->on('fuel_tanks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fuel_tank_flows', function (Blueprint $table) {
            //
        });
    }
};
