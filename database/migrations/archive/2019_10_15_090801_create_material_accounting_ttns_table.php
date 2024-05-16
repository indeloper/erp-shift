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
        Schema::create('material_accounting_ttns', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('operation_id');
            $table->string('main_entity');

            $table->string('take_time')->nullable();
            $table->string('take_fact_arrival_time')->nullable();
            $table->string('take_fact_departure_time')->nullable();
            $table->string('take_weight')->nullable();
            $table->string('take_places_count')->nullable();

            $table->string('give_time')->nullable();
            $table->string('give_fact_arrival_time')->nullable();
            $table->string('give_fact_departure_time')->nullable();
            $table->string('give_weight')->nullable();
            $table->string('give_places_count')->nullable();

            $table->string('entity')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('index')->nullable();

            $table->string('phone_number')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone_number')->nullable();
            $table->string('vehicle')->nullable();

            $table->string('vehicle_number')->nullable();
            $table->string('trailer')->nullable();
            $table->string('trailer_number')->nullable();
            $table->string('carrier')->nullable();
            $table->unsignedInteger('consignor');

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
        Schema::dropIfExists('material_accounting_ttns');
    }
};
