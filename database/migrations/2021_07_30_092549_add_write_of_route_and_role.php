<?php

use App\Models\Permission;
use App\Models\q3wMaterial\operations\q3wOperationRouteStage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWriteOfRouteAndRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $routeStageNames = [
            //Преобразование
            ['Инициализация', null, 4, 1],
            ['Уведомление ответственному лицу', 76, 4, 4],
            ['Ожидание ответственного лица', 77, 4, 3],
            ['Уведомление автору', 78, 4, 4],
            ['Завершена', 79, 4, 2],
            ['Отменена', 79, 4, 7]
        ];

        foreach ($routeStageNames as $routeStageName) {
            $routeStage = new q3wOperationRouteStage();
            $routeStage->parent_route_stage_id = $routeStageName[1];
            $routeStage->operation_route_id = $routeStageName[2];
            $routeStage->operation_route_stage_type_id = $routeStageName[3];
            $routeStage->name = $routeStageName[0];
            $routeStage->save();
        }

        $confirmToWriteOffPermission = new Permission();
        $confirmToWriteOffPermission->name = "Материальный учет: Подтверждение списания";
        $confirmToWriteOffPermission->codename = "material_accounting_write_off_confirmation";
        $confirmToWriteOffPermission->category = 7; // Категории описаны в модели "Permission"
        $confirmToWriteOffPermission->save();
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
