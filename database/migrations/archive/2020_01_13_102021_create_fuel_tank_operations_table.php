<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFuelTankOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fuel_tank_operations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('fuel_tank_id');
            $table->bigInteger('author_id');
            $table->bigInteger('object_id');
            $table->bigInteger('our_technic_id')->nullable();
            $table->bigInteger('contractor_id')->nullable();
            $table->float('value', 8, 3);
            $table->integer('type');
            $table->text('description')->nullable();
            $table->timestamp('operation_date');
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
        Schema::dropIfExists('fuel_tank_operations');
    }
}
