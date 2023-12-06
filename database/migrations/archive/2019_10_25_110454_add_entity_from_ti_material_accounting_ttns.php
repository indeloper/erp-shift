<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEntityFromTiMaterialAccountingTtns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('material_accounting_ttns', function (Blueprint $table) {
            $table->unsignedInteger('main_entity_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('material_accounting_ttns', function (Blueprint $table) {
            $table->dropColumn('main_entity_to');
        });
    }
}
