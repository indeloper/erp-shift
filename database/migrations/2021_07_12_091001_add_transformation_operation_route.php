<?php

use App\Models\q3wMaterial\operations\q3wOperationRouteStage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransformationOperationRoute extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $routeStageNames = [
            //Поставка
            ['Инициализация', null, 3, 1],
            ['Уведомление руководителю проектов', 70, 3, 4],
            ['Завершена', 71, 3, 2]
        ];

        foreach ($routeStageNames as $routeStageName) {
            $routeStage = new q3wOperationRouteStage();
            $routeStage->parent_route_stage_id = $routeStageName[1];
            $routeStage->operation_route_id = $routeStageName[2];
            $routeStage->operation_route_stage_type_id = $routeStageName[3];
            $routeStage->name = $routeStageName[0];
            $routeStage->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
