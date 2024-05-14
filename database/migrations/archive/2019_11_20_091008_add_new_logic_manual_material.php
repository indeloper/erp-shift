<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewLogicManualMaterial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manual_material_category_attributes', function (Blueprint $table) {
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('step')->nullable();
            $table->string('value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('manual_material_category_attributes', function (Blueprint $table) {
            $table->dropColumn('from');
            $table->dropColumn('to');
            $table->dropColumn('step');
            $table->dropColumn('value');
        });
    }
}
