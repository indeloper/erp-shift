<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAwaitingConfirmationColumnToFuelTanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->boolean('awaiting_confirmation')->nullable()->after('tank_number')->comment('Ожидает подтверждение перемещения и передачи ответственности');
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
            $table->dropColumn('awaiting_confirmation');
        });
    }
}
