<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToManual extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manual_relation_material_works', function (Blueprint $table) {
            $table->index('manual_material_id');
            $table->index('manual_work_id');
        });

        Schema::table('manual_material_parameters', function (Blueprint $table) {
            $table->index('attr_id');
            $table->index('mat_id');
        });

        Schema::table('manual_materials', function (Blueprint $table) {
            $table->index('id');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('manual_relation_material_works', function (Blueprint $table) {
            $table->dropIndex('manual_material_id');
            $table->dropIndex('manual_work_id');
        });

        Schema::table('manual_material_parameters', function (Blueprint $table) {
            $table->dropIndex('attr_id');
            $table->dropIndex('mat_id');
        });

        Schema::table('manual_materials', function (Blueprint $table) {
            $table->dropIndex('id');
            $table->dropIndex('category_id');
        });
    }
}