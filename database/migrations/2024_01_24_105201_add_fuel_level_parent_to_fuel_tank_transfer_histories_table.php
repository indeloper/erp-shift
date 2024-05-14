<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFuelLevelParentToFuelTankTransferHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuel_tank_transfer_histories', function (Blueprint $table) {
            $table->bigInteger('parent_fuel_level_id')->nullable()->unsigned()->after('fuel_level')->comment('Id записи о предыдущем остатке топлива');
            $table->foreign('parent_fuel_level_id')->references('id')->on('fuel_tank_transfer_histories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fuel_tank_transfer_histories', function (Blueprint $table) {
            $table->dropForeign(['parent_fuel_level_id']);
            $table->dropColumn('parent_fuel_level_id');
        });
    }
}
