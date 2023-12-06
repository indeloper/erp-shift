<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOurVehicleParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('our_vehicle_parameters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('vehicle_id');
            $table->unsignedInteger('characteristic_id');
            $table->string('value')->nullable();
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
        Schema::dropIfExists('our_vehicle_parameters');
    }
}
