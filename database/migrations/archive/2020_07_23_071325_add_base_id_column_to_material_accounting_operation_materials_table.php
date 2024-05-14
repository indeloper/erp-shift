<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBaseIdColumnToMaterialAccountingOperationMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('material_accounting_operation_materials', function (Blueprint $table) {
            $table->unsignedBigInteger('base_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('material_accounting_operation_materials', function (Blueprint $table) {
            $table->dropColumn('base_id');
        });
    }
}
