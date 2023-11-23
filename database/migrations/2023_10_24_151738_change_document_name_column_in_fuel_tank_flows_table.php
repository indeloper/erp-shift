<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDocumentNameColumnInFuelTankFlowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuel_tank_flows', function (Blueprint $table) {
            $table->dropColumn('document_date');
            $table->date('event_date')->after('id')->comment('Дата время факта события');
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
            $table->date('document_date');
            $table->dropColumn('event_date');
        });
    }
}
