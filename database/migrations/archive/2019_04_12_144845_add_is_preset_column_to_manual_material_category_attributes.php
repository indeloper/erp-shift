<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsPresetColumnToManualMaterialCategoryAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manual_material_category_attributes', function (Blueprint $table) {
            $table->boolean('is_preset')->nullable();
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
            $table->dropColumn('is_preset');
        });
    }
}