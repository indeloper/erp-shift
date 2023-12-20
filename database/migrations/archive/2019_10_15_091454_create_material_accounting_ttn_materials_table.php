<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMaterialAccountingTtnMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('material_accounting_ttn_materials', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ttn_id');
            $table->string('count');
            $table->string('unit');
            $table->unsignedInteger('material_id');
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
        Schema::dropIfExists('material_accounting_ttn_materials');
    }
}
