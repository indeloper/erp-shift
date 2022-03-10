<?php

use App\Models\q3wMaterial\operations\q3wOperationRouteStage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHumanReadableAliasColumnToQ3wOperationRouteStagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('q3w_operation_route_stages', function (Blueprint $table) {
            $table->string('human_readable_name')->nullable()->comment("Человекочитаемый псевдоним имени маршрута");
        });

        $operationRouteStage = q3wOperationRouteStage::find(4);
        $operationRouteStage->human_readable_name = 'Создание операции';
        $operationRouteStage->save();

        /*$operationRouteStage = q3wOperationRouteStage::find(6);
        $operationRouteStage->human_readable_name = 'Создание операции';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(25);
        $operationRouteStage->human_readable_name = 'Создание операции';
        $operationRouteStage->save();*/

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('q3w_operation_route_stages', function (Blueprint $table) {
            $table->dropColumn('human_readable_name');
        });
    }
}
