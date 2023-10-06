<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateFuelTankFlowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fuel_tank_flows', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('author_id')->comment('ID автора');
            $table->foreign('author_id')->references('id')->on('users');

            $table->bigInteger('fuel_tank_id')->unsigned()->comment('ID топливной емкости');
            $table->foreign('fuel_tank_id')->references('id')->on('fuel_tanks');

            $table->unsignedInteger('contractor_id')->nullable()->comment('ID контрагента');
            $table->foreign('contractor_id')->references('id')->on('contractors');

            $table->bigInteger('our_technic_id')->unsigned()->nullable()->comment('ID единицы техники');
            $table->foreign('our_technic_id')->references('id')->on('our_technics');

            $table->bigInteger('fuel_tank_flow_type_id')->unsigned()->comment('ID типа топливной операции');
            $table->foreign('fuel_tank_flow_type_id')->references('id')->on('fuel_tank_flow_types');

            $table->float('volume', 8, 3);

            $table->string('comment')->nullable()->comment('Комментарий');


            $table->timestamps();
            $table->softDeletes();
        });
        
        DB::statement("ALTER TABLE tank_fuel_flows COMMENT 'Движение топлива в емкостях'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fuel_tank_flows');
    }
}
