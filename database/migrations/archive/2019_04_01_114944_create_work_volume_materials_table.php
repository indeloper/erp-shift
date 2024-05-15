<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('work_volume_materials', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('work_volume_id');
            $table->unsignedInteger('manual_material_id');
            $table->boolean('is_our');
            $table->unsignedInteger('time')->nullable();
            $table->float('count', 10, 2)->nullable();
            $table->boolean('is_tongue')->default(1);
            $table->float('price_per_one', 15, 2)->nullable();
            $table->float('result_price', 15, 2)->nullable();
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
        Schema::dropIfExists('work_volume_materials');
    }
};
