<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftDeletesToMatAccTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('material_accounting_operations', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('material_accounting_operation_materials', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('material_accounting_material_files', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('material_accounting_operations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('material_accounting_operation_materials', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('material_accounting_material_files', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
