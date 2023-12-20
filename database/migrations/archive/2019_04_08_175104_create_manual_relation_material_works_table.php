<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManualRelationMaterialWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manual_relation_material_works', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('manual_material_id')->nullable();
            $table->unsignedInteger('manual_work_id')->nullable();
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
        Schema::dropIfExists('manual_relation_material_works');
    }
}
