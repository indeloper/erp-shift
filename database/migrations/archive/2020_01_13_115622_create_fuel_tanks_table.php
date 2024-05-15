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
        Schema::create('fuel_tanks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('fuel_level', 13, 3);
            $table->string('tank_number', 10)->index();
            $table->unsignedBigInteger('object_id')->index();
            $table->timestamp('explotation_start');
            $table->timestamps();
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
        Schema::dropIfExists('fuel_tanks');
    }
};
