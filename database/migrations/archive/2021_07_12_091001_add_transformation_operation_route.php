<?php

use App\Models\q3wMaterial\operations\q3wMaterialOperation;
use App\Models\q3wMaterial\operations\q3wOperationComment;
use App\Models\q3wMaterial\operations\q3wOperationMaterial;
use App\Models\q3wMaterial\operations\q3wOperationRouteStage;
use App\Models\q3wMaterial\operations\q3wTransformOperationStage;
use App\Models\q3wMaterial\q3wMaterialSnapshot;
use App\Models\q3wMaterial\q3wMaterialSnapshotMaterial;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            //Преобразование
            ['Инициализация', null, 3, 1],
            ['Уведомление руководителю проектов', 69, 3, 4],
            ['Ожидание руководителя проектов', 70, 3, 3],
            ['Уведомление автору', 71, 3, 4],
            ['Завершена', 72, 3, 2],
            ['Отменена', 72, 3, 7],
        ];

        foreach ($routeStageNames as $routeStageName) {
            $routeStage = new q3wOperationRouteStage();
            $routeStage->parent_route_stage_id = $routeStageName[1];
            $routeStage->operation_route_id = $routeStageName[2];
            $routeStage->operation_route_stage_type_id = $routeStageName[3];
            $routeStage->name = $routeStageName[0];
            $routeStage->save();
        }

        Schema::create('q3w_transform_operation_stages', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->string('name')->comment('Наименование');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('q3w_operation_materials', function (Blueprint $table) {
            $table->bigInteger('transform_operation_stage_id')->unsigned()->nullable()->index()->comment('Этап преобразования материала');

            $table->foreign('transform_operation_stage_id')->references('id')->on('q3w_transform_operation_stages');
        });

        $transformStageNames = [
            'Материал до преобразования',
            'Материал после преобразования',
            'Остатки исходного материала',
        ];

        foreach ($transformStageNames as $transformStageName) {
            $transformStage = new q3wTransformOperationStage();
            $transformStage->name = $transformStageName;
            $transformStage->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $operations = q3wMaterialOperation::where('operation_route_id', 3);
        $snapshots = q3wMaterialSnapshot::whereIn('operation_id', $operations->pluck('id')->all());

        q3wMaterialSnapshotMaterial::whereIn('snapshot_id', $snapshots->pluck('id')->all())->forceDelete();
        q3wOperationComment::whereIn('material_operation_id', $operations->pluck('id')->all())->forceDelete();
        q3wOperationMaterial::whereIn('material_operation_id', $operations->pluck('id')->all())->forceDelete();

        $snapshots->forceDelete();
        $operations->forceDelete();

        q3wOperationRouteStage::where('operation_route_id', 3)->forceDelete();

        Schema::table('q3w_operation_materials', function (Blueprint $table) {
            $table->dropForeign(['transform_operation_stage_id']);
            $table->dropColumn('transform_operation_stage_id');
        });

        Schema::dropIfExists('q3w_transform_operation_stages');
    }
}
