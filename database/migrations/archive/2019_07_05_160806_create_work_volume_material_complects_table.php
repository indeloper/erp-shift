<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkVolumeMaterialComplectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_volume_material_complects', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('wv_material_id');
            $table->unsignedInteger('work_volume_id');
            $table->string('name');
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
        Schema::dropIfExists('work_volume_material_complects');
    }
}
