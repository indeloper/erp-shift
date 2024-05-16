<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConfirmMovingColumnToFuelTankTransferHystoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuel_tank_transfer_hystories', function (Blueprint $table) {
            $table->boolean('tank_moving_confirmation')->nullable()->after('fuel_tank_id')->comment('Подтвержждение перемещения и передачи ответственности');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fuel_tank_transfer_hystories', function (Blueprint $table) {
            $table->dropColumn('tank_moving_confirmation');
        });
    }
}
