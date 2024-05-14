<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIsDisplayToManualMaterialCategoryAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manual_material_category_attributes', function (Blueprint $table) {
            $table->boolean('is_display')->default(1);
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
            $table->dropColumn('is_display');
        });
    }
}
