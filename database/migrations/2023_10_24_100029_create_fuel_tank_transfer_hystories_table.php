<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateFuelTankTransferHystoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fuel_tank_transfer_hystories', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('author_id')->comment('ID автора');
            $table->foreign('author_id')->references('id')->on('users');

            $table->bigInteger('fuel_tank_id')->unsigned()->comment('ID топливной емкости');
            $table->foreign('fuel_tank_id')->references('id')->on('fuel_tanks');

            $table->unsignedInteger('object_id')->nullable()->comment('ID объекта');
            $table->foreign('object_id')->references('id')->on('project_objects');

            $table->unsignedInteger('previous_object_id')->nullable()->comment('ID предыдущего объекта');
            $table->foreign('previous_object_id')->references('id')->on('project_objects');

            $table->unsignedInteger('responsible_id')->nullable()->comment('ID ответственного');
            $table->foreign('responsible_id')->references('id')->on('users');

            $table->unsignedInteger('previous_responsible_id')->nullable()->comment('ID предыдущего ответственного');
            $table->foreign('previous_responsible_id')->references('id')->on('users');
            
            $table->integer('volume')->nullable()->comment('Количество топлива');

            $table->timestamps();
            $table->softDeletes();

        });

        DB::statement("ALTER TABLE fuel_tank_transfer_hystories COMMENT 'История перемещения и передачи ответственности по топливным емкостям'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fuel_tank_transfer_hystories');
    }
}
