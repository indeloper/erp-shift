<?php

use App\Models\q3wMaterial\operations\q3wOperationRouteStage;
use App\Models\q3wMaterial\operations\q3wOperationRouteStageType;
use App\models\q3wMaterial\operations\q3wOperationRoute;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQ3wMaterialOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('q3w_operation_routes', function (Blueprint $table) {
            $table->integerIncrements('id')->comment('Уникальный идентификатор');
            $table->string('name')->comment('Наименование маршрута операции');
            //?$table->string('controller')->comment('Класс контроллера для операции');

            $table->timestamps();
            $table->softDeletes();
        });

        $routeNames = ['Поставка', 'Перемещение', 'Преобразование', 'Списание'];

        foreach ($routeNames as $routeName) {
            $route = new q3wOperationRoute();
            $route -> name = $routeName;
            $route -> save();
        }

        Schema::create('q3w_operation_route_stage_types', function (Blueprint $table) {
            $table->integerIncrements('id')->comment('Уникальный идентификатор');
            $table->string('name')->comment('Наименование маршрута операции');
            //?$table->string('controller')->comment('Класс контроллера для операции');

            $table->timestamps();
            $table->softDeletes();
        });

        $routeStageTypeNames = ['Инициализация', 'Завершение', 'Уведомление', 'Согласование'];

        foreach ($routeStageTypeNames as $routeStageTypeName) {
            $routeStageType = new q3wOperationRouteStageType();
            $routeStageType -> name = $routeStageTypeName;
            $routeStageType -> save();
        }

        Schema::create('q3w_operation_route_stages', function (Blueprint $table) {
            $table->integerIncrements('id')->comment('Уникальный идентификатор');
            $table->integer('parent_route_stage_id')->unsigned()->nullable()->index()->comment('ID предыдущего этапа (q3w_operation_route_stages)');
            $table->integer('operation_route_id')->unsigned()->index()->comment('Маршрут');
            $table->integer('operation_route_stage_type_id')->unsigned()->index()->comment('Тип маршрута');
            $table->string('name')->comment('Наименование этапа маршрута');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('operation_route_id')->references('id')->on('q3w_operation_routes');
            $table->foreign('operation_route_stage_type_id')->references('id')->on('q3w_operation_route_stage_types');
        });

        $routeStageNames = [['Инициализация', null, 1, 1], ['Уведомление руководителю проектов', 1, 1, 4], ['Заявка завершена', 2, 1, 2]];

        foreach ($routeStageNames as $routeStageName) {
            $routeStage = new q3wOperationRouteStage();
            $routeStage -> parent_route_stage_id = $routeStageName[1];
            $routeStage -> operation_route_id = $routeStageName[2];
            $routeStage -> operation_route_stage_type_id = $routeStageName[3];
            $routeStage -> name = $routeStageName[0];
            $routeStage -> save();
        }

        Schema::create('q3w_material_operations', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->integer('operation_route_id')->unsigned()->index()->comment('Идентификатор типа операции');

            $table->integer('source_project_object_id')->unsigned()->nullable()->index()->comment('Идентификатор объекта, с которого отправляется материал');
            $table->integer('destination_project_object_id')->unsigned()->nullable()->index()->comment('Идентификатор идентификатор объекта, куда должен прибыть материал');

            $table->integer('contractor_id')->unsigned()->nullable()->index()->comment('Идентификатор контрагента (поставщика)');
            //Договор $table->time('contractor_id')->unsigned()->nullable()->index()->comment('Идентификатор контрагента (поставщика)');

            $table->timestamp('date_start')->comment('Дата начала');
            $table->timestamp('date_end')->nullable()->comment('Дата окончания ');

            $table->integer('creator_user_id')->unsigned()->index()->comment('ID пользователя, создавшего операцию');
            $table->integer('responsible_user_id')->unsigned()->index()->comment('ID ответственного пользователя');

            $table->text('creator_comment')->comment('Комментарий пользователя');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('operation_route_id')->references('id')->on('q3w_operation_routes');
            $table->foreign('source_project_object_id')->references('id')->on('project_objects');
            $table->foreign('destination_project_object_id')->references('id')->on('project_objects');
            $table->foreign('contractor_id')->references('id')->on('contractors');
            $table->foreign('creator_user_id')->references('id')->on('users');
            $table->foreign('responsible_user_id')->references('id')->on('users');
        });

        Schema::create('q3w_operation_materials', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->bigInteger('material_operation_id')->unsigned()->comment('Идентификатор операции')->index();
            $table->bigInteger('standard_id')->unsigned()->comment('Идентификатор эталона')->index();
            $table->integer('amount')->unsigned()->nullable()->comment('Количество в штуках (для штучного учета)');
            $table->double('quantity')->unsigned()->comment('Количество в единицах измерения');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('standard_id')->references('id')->on('q3w_material_standards');
            $table->foreign('material_operation_id')->references('id')->on('q3w_material_operations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('q3w_operation_materials');
        Schema::dropIfExists('q3w_material_operations');
        Schema::dropIfExists('q3w_operation_route_stages');
        Schema::dropIfExists('q3w_operation_route_stage_types');
        Schema::dropIfExists('q3w_operation_routes');
    }
}
