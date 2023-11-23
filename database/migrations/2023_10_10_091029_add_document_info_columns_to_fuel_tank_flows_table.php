<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDocumentInfoColumnsToFuelTankFlowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuel_tank_flows', function (Blueprint $table) {
            $table->string('document')->nullable()->after('volume')->comment('Реквизиты документа');
            $table->date('document_date')->nullable()->after('document')->comment('Дата документа');
        });

        Schema::table('fuel_tank_flows', function (Blueprint $table) {
            $table->dropColumn('comment');
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
            $table->dropColumn('document');
            $table->dropColumn('document_date');
        });
    }
}
