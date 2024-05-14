<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaterialAccountingBasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('material_accounting_bases', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('object_id');
            $table->unsignedInteger('manual_material_id');
            $table->string('date');

            $table->string('count');

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
        Schema::dropIfExists('material_accounting_bases');
    }
}
