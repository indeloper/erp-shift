<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUsedColumnForMaterialAccountingOperationMaterials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('material_accounting_operation_materials', function (Blueprint $table) {
            $table->boolean('used')->default(0);
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
            $table->dropColumn('used');
        });
    }
}