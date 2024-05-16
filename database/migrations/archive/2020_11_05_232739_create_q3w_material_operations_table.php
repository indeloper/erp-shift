<?php

use App\Models\q3wMaterial\operations\q3wOperationFileType;
use App\Models\q3wMaterial\operations\q3wOperationRouteStage;
use App\Models\q3wMaterial\operations\q3wOperationRouteStageType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
            DB::table('q3w_operation_routes')->insert(['name' => $routeName]);
            /*$route = new App\models\q3wMaterial\operations\q3wOperationRoute();
            $route -> name = $routeName;
            $route -> save();*/
        }

        Schema::create('q3w_operation_route_stage_types', function (Blueprint $table) {
            $table->integerIncrements('id')->comment('Уникальный идентификатор');
            $table->string('name')->comment('Наименование маршрута операции');
            //?$table->string('controller')->comment('Класс контроллера для операции');

            $table->timestamps();
            $table->softDeletes();
        });

        $routeStageTypeNames = ['Инициализация', 'Завершение', 'Согласование', 'Уведомление', 'Ожидание', 'Конфликт', 'Отмена'];

        foreach ($routeStageTypeNames as $routeStageTypeName) {
            $routeStageType = new q3wOperationRouteStageType();
            $routeStageType->name = $routeStageTypeName;
            $routeStageType->save();
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

        $routeStageNames = [
            //Поставка
            ['Инициализация', null, 1, 1],
            ['Уведомление руководителю проектов', 1, 1, 4],
            ['Завершена', 2, 1, 2],
            //Перемещение
            ['Инициализация', null, 2, 1],
            //Создал отправитель
            ['Уведомление получателю', 4, 2, 4],
            ['Ожидание получателя', 5, 2, 5],
            //Конфликта нет
            ['Уведомление отправителю', 6, 2, 4],
            ['Завершена', 7, 2, 2],
            //Конфликт есть
            ['Уведомление руководителю получателя', 6, 2, 4],
            ['Уведомление отправителю', 43, 2, 4],
            ['Конфликт у получателя', 10, 2, 6],
            //Отправитель согласен с конфликтом
            ['Уведомление получателю', 11, 2, 4],
            ['Уведомление руководителю получателя', 12, 2, 4],
            ['Уведомление руководителю отправителя', 13, 2, 4],
            ['Завершена', 14, 2, 2],
            //Отправитель не согласен
            ['Уведомление получателю', 11, 2, 4],
            ['Уведомление руководителю получателя', 16, 2, 4],
            ['Уведомление руководителю отправителя', 17, 2, 4],
            ['Конфликт у получателя (решение руководителя)', 18, 2, 6],
            ['Уведомление получателю', 19, 2, 4],
            ['Уведомление руководителю отправителя', 20, 2, 4],
            ['Уведомление отправителю', 21, 2, 4],
            ['Завершена', 22, 2, 2],

            //Создал получаетель
            ['Уведомление отправителю', 4, 2, 4],
            ['Ожидание отправителя', 24, 2, 5],
            //Конфликта нет
            ['Уведомление получателя', 25, 2, 4],
            ['Завершена', 26, 2, 2],
            //Конфликт есть
            ['Уведомление руководителю отправителя', 25, 2, 4],
            ['Уведомление получателю', 44, 2, 4],
            ['Конфликт у отправителя', 29, 2, 6],
            //Отправитель согласен с конфликтом
            ['Уведомление отправителю', 30, 2, 4],
            ['Уведомление руководителю отправителя', 31, 2, 4],
            ['Уведомление руководителю получателя', 32, 2, 4],
            ['Завершена', 33, 2, 2],
            //Отправитель не согласен
            ['Уведомление отправителю', 30, 2, 4],
            ['Уведомление руководителю отправителя', 35, 2, 4],
            ['Уведомление руководителю получателя', 36, 2, 4],
            ['Конфликт у отправителя (решение руководителя)', 37, 2, 6],
            ['Уведомление отправителю', 38, 2, 4],
            ['Уведомление руководителю получателя', 39, 2, 4],
            ['Уведомление получателю', 40, 2, 4],
            ['Завершена', 41, 2, 2],
            ['Уведомление руководителю отправителя', 9, 2, 4],
            ['Уведомление руководителю получателя', 28, 2, 4],
            //Отправитель отменил заявку после этапа 6
            ['Уведомление получателю', 6, 2, 4],
            ['Уведомление руководителю отправителя', 45, 2, 4],
            ['Уведомление руководителю получателя', 46, 2, 4],
            ['Отменена', 47, 2, 7],
            //Получатель отменил заявку после этапа 11
            ['Уведомление отправителю', 11, 2, 4],
            ['Уведомление руководителю отправителя', 49, 2, 4],
            ['Уведомление руководителю получателя', 50, 2, 4],
            ['Отменена', 51, 2, 7],
            //РП Получателя отменил заявку после этапа 19
            ['Уведомление получателю', 19, 2, 4],
            ['Уведомление отправителю', 53, 2, 4],
            ['Уведомление руководителю отправителя', 54, 2, 4],
            ['Отменена', 55, 2, 7],
            //Получатель отменил заявку после этапа 25
            ['Уведомление отправителю', 25, 2, 4],
            ['Уведомление руководителю получателя', 57, 2, 4],
            ['Уведомление руководителю отправителя', 58, 2, 4],
            ['Отменена', 59, 2, 7],
            //Отправитель отменил заявку после этапа 30
            ['Уведомление получателю', 30, 2, 4],
            ['Уведомление руководителю отправителя', 61, 2, 4],
            ['Уведомление руководителю получателя', 62, 2, 4],
            ['Отменена', 63, 2, 7],
            //РП отправителя отменил заявку после этапа 36
            ['Уведомление получателю', 36, 2, 4],
            ['Уведомление отправителю', 65, 2, 4],
            ['Уведомление руководителю получателя', 66, 2, 4],
            ['Отменена', 67, 2, 7],
        ];

        foreach ($routeStageNames as $routeStageName) {
            $routeStage = new q3wOperationRouteStage();
            $routeStage->parent_route_stage_id = $routeStageName[1];
            $routeStage->operation_route_id = $routeStageName[2];
            $routeStage->operation_route_stage_type_id = $routeStageName[3];
            $routeStage->name = $routeStageName[0];
            $routeStage->save();
        }

        Schema::create('q3w_material_operations', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->integer('operation_route_id')->unsigned()->index()->comment('Идентификатор типа операции');
            $table->integer('operation_route_stage_id')->unsigned()->index()->comment('Идентификатор этапа операции');

            $table->integer('source_project_object_id')->unsigned()->nullable()->index()->comment('Идентификатор объекта, с которого отправляется материал');
            $table->integer('destination_project_object_id')->unsigned()->nullable()->index()->comment('Идентификатор идентификатор объекта, куда должен прибыть материал');

            $table->integer('contractor_id')->unsigned()->nullable()->index()->comment('Идентификатор контрагента (поставщика)');
            $table->integer('consignment_note_number')->unsigned()->comment('Номер ТТН');

            $table->timestamp('operation_date')->comment('Дата начала');

            $table->integer('creator_user_id')->unsigned()->index()->comment('ID пользователя, создавшего операцию');
            $table->integer('source_responsible_user_id')->unsigned()->nullable()->index()->comment('ID ответственного пользователя со стороны объекта-отправителя');
            $table->integer('destination_responsible_user_id')->unsigned()->nullable()->index()->comment('ID ответственного пользователя со стороны объекта-получателя');

            $table->text('creator_comment')->comment('Комментарий пользователя');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('operation_route_id')->references('id')->on('q3w_operation_routes');
            $table->foreign('operation_route_stage_id')->references('id')->on('q3w_operation_route_stages');
            $table->foreign('source_project_object_id')->references('id')->on('project_objects');
            $table->foreign('destination_project_object_id')->references('id')->on('project_objects');
            $table->foreign('contractor_id')->references('id')->on('contractors');

            $table->foreign('creator_user_id')->references('id')->on('users');
            $table->foreign('source_responsible_user_id')->references('id')->on('users');
            $table->foreign('destination_responsible_user_id')->references('id')->on('users');
        });

        Schema::create('q3w_operation_materials', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->bigInteger('material_operation_id')->unsigned()->comment('Идентификатор операции')->index();
            $table->integer('standard_id')->unsigned()->comment('Идентификатор эталона')->index();

            $table->integer('amount')->unsigned()->nullable()->comment('Количество в штуках');
            $table->integer('initial_amount')->unsigned()->nullable()->comment('Количество в штуках, которые указал инициатор');

            $table->double('quantity')->unsigned()->comment('Количество в единицах измерения');
            $table->double('initial_quantity')->unsigned()->comment('Количество в единицах измерения, которые указал инициатор');

            $table->json('edit_states')->nullable()->comment('Массив состояний записи в процессе работы с операцией');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('standard_id')->references('id')->on('q3w_material_standards');
            $table->foreign('material_operation_id')->references('id')->on('q3w_material_operations');
        });

        Schema::create('q3w_operation_comments', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->bigInteger('material_operation_id')->unsigned()->comment('Идентификатор операции')->index();
            $table->integer('operation_route_stage_id')->unsigned()->comment('Идентификатор этапа (статуса) операции')->index();
            $table->integer('user_id')->unsigned()->comment('Идентификатор пользователя, оставившего операцию')->index();
            $table->text('comment')->comment('Комментарий пользователя');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('material_operation_id')->references('id')->on('q3w_material_operations');
            $table->foreign('operation_route_stage_id')->references('id')->on('q3w_operation_route_stages');
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('q3w_operation_file_types', function (Blueprint $table) {
            $table->integerIncrements('id')->comment('Уникальный идентификатор');
            $table->string('name')->comment('Наименование');
            $table->string('string_identifier')->unique()->comment('Строковый идентификатор');

            $table->timestamps();
            $table->softDeletes();
        });

        $fileTypes = [['ТТН', 'consignment-note-photo'], ['Фото транспорта спереди', 'frontal-vehicle-photo'], ['Фото транспорта сзади', 'behind-vehicle-photo'], ['Фото материалов', 'materials-photo']];

        foreach ($fileTypes as $fileTypeElement) {
            $fileType = new q3wOperationFileType();
            $fileType->name = $fileTypeElement[0];
            $fileType->string_identifier = $fileTypeElement[1];
            $fileType->save();
        }

        Schema::create('q3w_operation_files', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->bigInteger('material_operation_id')->nullable()->unsigned()->comment('Идентификатор операции')->index();
            $table->integer('operation_route_stage_id')->nullable()->unsigned()->comment('Идентификатор этапа (статуса) операции')->index();
            $table->integer('upload_file_type')->unsigned()->comment('Идентификатор типа файла');
            $table->string('file_name')->comment('Имя файла');
            $table->string('file_path')->comment('Относительный путь к файлу');
            $table->string('original_file_name')->comment('Оригинальное имя файла');
            $table->integer('user_id')->unsigned()->comment('Имя пользователя, загрузившего файл')->index();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('material_operation_id')->references('id')->on('q3w_material_operations');
            $table->foreign('operation_route_stage_id')->references('id')->on('q3w_operation_route_stages');
            $table->foreign('upload_file_type')->references('id')->on('q3w_operation_file_types');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('q3w_operation_files');
        Schema::dropIfExists('q3w_operation_file_types');
        Schema::dropIfExists('q3w_operation_comments');
        Schema::dropIfExists('q3w_operation_materials');
        Schema::dropIfExists('q3w_material_operations');
        Schema::dropIfExists('q3w_operation_route_stages');
        Schema::dropIfExists('q3w_operation_route_stage_types');
        Schema::dropIfExists('q3w_operation_routes');
    }
}
