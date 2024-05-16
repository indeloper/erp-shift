<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        Schema::table('manual_copied_works', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('manual_materials', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('manual_material_categories', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('manual_material_category_attributes', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('manual_material_category_relation_to_works', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('manual_material_parameters', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('manual_material_passports', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('manual_nodes', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('manual_node_categories', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('manual_node_materials', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('manual_relation_material_works', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::dropIfExists('manual_tongues');
        Schema::table('manual_works', function (Blueprint $table) {
            $table->softDeletes();
        });

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
