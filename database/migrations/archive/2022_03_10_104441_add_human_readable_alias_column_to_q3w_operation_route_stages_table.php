<?php

use App\Models\q3wMaterial\operations\q3wOperationRouteStage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('q3w_operation_route_stages', function (Blueprint $table) {
            $table->string('human_readable_name')->nullable()->comment('Человекочитаемый псевдоним имени маршрута');
        });

        // Поставка
        $operationRouteStage = q3wOperationRouteStage::find(3);
        $operationRouteStage->human_readable_name = 'Создание операции «Поставка»';
        $operationRouteStage->save();

        // Перемещение
        $operationRouteStage = q3wOperationRouteStage::find(4);
        $operationRouteStage->human_readable_name = 'Создание операции «Перемещение»';
        $operationRouteStage->save();

        // Инициатор - отправитель
        $operationRouteStage = q3wOperationRouteStage::find(48);
        $operationRouteStage->human_readable_name = 'Отмена операции отправителем';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(8);
        $operationRouteStage->human_readable_name = 'Завершение операции получателем';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(11);
        $operationRouteStage->human_readable_name = 'Получателем сделаны изменения в списке материалов';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(52);
        $operationRouteStage->human_readable_name = 'Отмена конфликтной операции отправителем';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(15);
        $operationRouteStage->human_readable_name = 'Завершение конфликтной операции — отправитель подтвердил изменения';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(19);
        $operationRouteStage->human_readable_name = 'Постановка конфликтной операции под контроль руководителя получателя';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(56);
        $operationRouteStage->human_readable_name = 'Отмена конфликтной операции руководителем получателя';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(23);
        $operationRouteStage->human_readable_name = 'Завершение конфликтной операции руководителем получателя';
        $operationRouteStage->save();

        // Инициатор - получатель
        $operationRouteStage = q3wOperationRouteStage::find(60);
        $operationRouteStage->human_readable_name = 'Отмена операции получателем';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(27);
        $operationRouteStage->human_readable_name = 'Завершение операции отправителем';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(30);
        $operationRouteStage->human_readable_name = 'Отправителем сделаны изменения в списке материалов';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(64);
        $operationRouteStage->human_readable_name = 'Отмена конфликтной операции получателем';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(34);
        $operationRouteStage->human_readable_name = 'Завершение конфликтной операции — получатель подтвердил изменения';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(38);
        $operationRouteStage->human_readable_name = 'Постановка конфликтной операции под контроль руководителя отправителя';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(68);
        $operationRouteStage->human_readable_name = 'Отмена конфликтной операции руководителем отправителя';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(42);
        $operationRouteStage->human_readable_name = 'Завершение конфликтной операции руководителем отправителя';
        $operationRouteStage->save();

        // Преобразование
        $operationRouteStage = q3wOperationRouteStage::find(69);
        $operationRouteStage->human_readable_name = 'Создание операции «Преобразование»';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(73);
        $operationRouteStage->human_readable_name = 'Завершение операции руководителем';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(74);
        $operationRouteStage->human_readable_name = 'Отмена операции руководителем';
        $operationRouteStage->save();

        // Списание
        $operationRouteStage = q3wOperationRouteStage::find(75);
        $operationRouteStage->human_readable_name = 'Создание операции «Списание»';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(79);
        $operationRouteStage->human_readable_name = 'Согласование операции с руководителем';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(81);
        $operationRouteStage->human_readable_name = 'Завершение операции ответственным лицом';
        $operationRouteStage->save();

        $operationRouteStage = q3wOperationRouteStage::find(82);
        $operationRouteStage->human_readable_name = 'Отмена операции';
        $operationRouteStage->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('q3w_operation_route_stages', function (Blueprint $table) {
            $table->dropColumn('human_readable_name');
        });
    }
};
