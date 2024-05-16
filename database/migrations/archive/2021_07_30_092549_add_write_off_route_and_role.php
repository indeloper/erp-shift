<?php

use App\Models\Permission;
use App\Models\q3wMaterial\operations\q3wMaterialOperation;
use App\Models\q3wMaterial\operations\q3wOperationComment;
use App\Models\q3wMaterial\operations\q3wOperationMaterial;
use App\Models\q3wMaterial\operations\q3wOperationRouteStage;
use App\Models\q3wMaterial\q3wMaterialSnapshot;
use App\Models\q3wMaterial\q3wMaterialSnapshotMaterial;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;

class AddWriteOffRouteAndRole extends Migration
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
            ['Уведомление руководителю проектов', 75, 4, 4],
            ['Ожидание руководителя проектов', 76, 4, 3],
            ['Уведомление ответственному лицу', 77, 4, 4],
            ['Ожидание ответственного лица', 78, 4, 3],
            ['Уведомление автору', 79, 4, 4],
            ['Завершена', 80, 4, 2],
            ['Отменена', 80, 4, 7],
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
        $confirmToWriteOffPermission->name = 'Материальный учет: Подтверждение списания';
        $confirmToWriteOffPermission->codename = 'material_accounting_write_off_confirmation';
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
        $confirmToWriteOffPermission = Permission::where('codename', 'material_accounting_write_off_confirmation')->first();

        UserPermission::where('permission_id', $confirmToWriteOffPermission->id)->forceDelete();

        $confirmToWriteOffPermission->forceDelete();

        $operations = q3wMaterialOperation::where('operation_route_id', 4);
        $snapshots = q3wMaterialSnapshot::whereIn('operation_id', $operations->pluck('id')->all());

        q3wMaterialSnapshotMaterial::whereIn('snapshot_id', $snapshots->pluck('id')->all())->forceDelete();
        q3wOperationComment::whereIn('material_operation_id', $operations->pluck('id')->all())->forceDelete();
        q3wOperationMaterial::whereIn('material_operation_id', $operations->pluck('id')->all())->forceDelete();

        $snapshots->forceDelete();
        $operations->forceDelete();

        q3wOperationRouteStage::where('operation_route_id', 4)->forceDelete();
    }
}
