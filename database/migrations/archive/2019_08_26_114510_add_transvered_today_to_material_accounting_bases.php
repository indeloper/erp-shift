<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransveredTodayToMaterialAccountingBases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // column need to know
        Schema::table('material_accounting_bases', function (Blueprint $table) {
            $table->boolean('transferred_today')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('material_accounting_bases', function (Blueprint $table) {
            $table->dropColumn('transferred_today');
        });
    }
}
