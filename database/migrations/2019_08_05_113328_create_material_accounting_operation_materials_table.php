<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMaterialAccountingOperationMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('material_accounting_operation_materials', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('operation_id');
            $table->unsignedInteger('manual_material_id');

            $table->string('count')->default(0);
            $table->unsignedInteger('unit');

            $table->unsignedInteger('type');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('material_accounting_operation_materials');
    }
}
