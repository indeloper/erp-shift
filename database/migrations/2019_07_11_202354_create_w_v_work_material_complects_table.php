<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWVWorkMaterialComplectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('w_v_work_material_complects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('complect_name');
            $table->unsignedInteger('work_volume_id');
            $table->unsignedInteger('wv_work_id');
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
        Schema::dropIfExists('w_v_work_material_complects');
    }
}
