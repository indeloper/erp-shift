<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOwnerIdColumnToFuelTankOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuel_tank_operations', function (Blueprint $table) {
            $table->integer('owner_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fuel_tank_operations', function (Blueprint $table) {
            $table->dropColumn('owner_id');
        });
    }
}
