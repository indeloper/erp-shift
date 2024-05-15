<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        Schema::create('notification_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('group');
            $table->text('name');
            $table->boolean('for_everyone')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('notification_types')->insert([
            // Tasks-related notifications
            [
                'id' => 1,
                'group' => 1,
                'name' => 'Уведомление о скором завершении времени на выполнение задачи',
                'for_everyone' => 1,
            ],
            [
                'id' => 2,
                'group' => 1,
                'name' => 'Уведомление о завершении времени на выполнение задачи',
                'for_everyone' => 1,
            ],
            [
                'id' => 3,
                'group' => 1,
                'name' => 'Уведомление о закрытии задачи',
                'for_everyone' => 1,
            ],
            [
                'id' => 4,
                'group' => 1,
                'name' => 'Уведомление о задаче Обработка входящего звонка',
                'for_everyone' => 0, // 'cus we don't use it now
            ],
            [
                'id' => 5,
                'group' => 1,
                'name' => 'Уведомление о просроченной задаче пользователя',
                'for_everyone' => 0, // for groups and permissions
            ],
            [
                'id' => 6,
                'group' => 1,
                'name' => 'Уведомление о передаче задачи новому ответственному лицу',
                'for_everyone' => 0, // for groups and users
            ],
            [
                'id' => 7,
                'group' => 1,
                'name' => 'Уведомление о том, что задача отложена и закрыта',
                'for_everyone' => 0, // for groups and users
            ],
            // Material Accounting notifications
            [
                'id' => 8,
                'group' => 2,
                'name' => 'Уведомление о задаче Контроль списания',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 9,
                'group' => 2,
                'name' => 'Уведомление о задаче Запрос на редактирование операции частичного закрытия',
                'for_everyone' => 0, // for permission
            ],
            [
                'id' => 10,
                'group' => 2,
                'name' => 'Уведомление о задаче Запрос на удаление операции частичного закрытия',
                'for_everyone' => 0, // for permission
            ],
            [
                'id' => 11,
                'group' => 2,
                'name' => 'Уведомление о назначении на позицию ответственного в операцию',
                'for_everyone' => 1,
            ],
            [
                'id' => 12,
                'group' => 2,
                'name' => 'Уведомление о расхождении отправленного и полученного материала',
                'for_everyone' => 0, // for permission
            ],
            [
                'id' => 13,
                'group' => 2,
                'name' => 'Уведомление об отклонении операции списания',
                'for_everyone' => 0, // for permission
            ],
            // Tech Support Notifications
            [
                'id' => 14,
                'group' => 3,
                'name' => 'Уведомление о проведении технических работ',
                'for_everyone' => 1,
            ],
            [
                'id' => 15,
                'group' => 3,
                'name' => 'Уведомление о завершении проведения технических работ',
                'for_everyone' => 1,
            ],
            [
                'id' => 16,
                'group' => 3,
                'name' => 'Уведомление о задаче Согласование дополнительных работ',
                'for_everyone' => 0, // for group
            ],
            // Contractor Notifications
            [
                'id' => 17,
                'group' => 4,
                'name' => 'Уведомление о задаче Контроль удаления контрагента',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 18,
                'group' => 4,
                'name' => 'Уведомление о том, что пользователь создал контрагента без контактов',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 19,
                'group' => 4,
                'name' => 'Уведомление о том, что необходимо заполнить контактов контрагента',
                'for_everyone' => 0, // for permission
            ],
            [
                'id' => 20,
                'group' => 4,
                'name' => 'Уведомление о решении задачи Контроль удаления контрагента',
                'for_everyone' => 0, // for permission
            ],
            // Work Volumes Notifications
            [
                'id' => 21,
                'group' => 5,
                'name' => 'Уведомление о создании задачи Расчёт ОР (шпунтовое направление)',
                'for_everyone' => 0, // for groups
            ],
            [
                'id' => 22,
                'group' => 5,
                'name' => 'Уведомление о создании задачи Расчёт ОР (свайное направление)',
                'for_everyone' => 0, // for user
            ],
            [
                'id' => 23,
                'group' => 5,
                'name' => 'Уведомление о создании задачи Обработка заявки на ОР',
                'for_everyone' => 0, // for group and user
            ],
            [
                'id' => 24,
                'group' => 5,
                'name' => 'Уведомление о создании задачи Назначение ответственного за ОР (шпунтовое направление)',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 25,
                'group' => 5,
                'name' => 'Уведомление о создании задачи Контроль выполнения ОР (шпунтовое направление)',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 26,
                'group' => 5,
                'name' => 'Уведомление о создании заявки на редактирование ОР',
                'for_everyone' => 0, // for group and user
            ],
            [
                'id' => 27,
                'group' => 5,
                'name' => 'Уведомление об обработке заявки на ОР',
                'for_everyone' => 0, // for group and user
            ],
            // Commercial Offers Notifications
            [
                'id' => 28,
                'group' => 6,
                'name' => 'Уведомление о создании задачи Формирование КП (шпунтовое направление)',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 29,
                'group' => 6,
                'name' => 'Уведомление о создании задачи Формирование КП (свайное направление)',
                'for_everyone' => 0, // for user
            ],
            [
                'id' => 30,
                'group' => 6,
                'name' => 'Уведомление о создании задачи Назначение ответственного за КП (шпунтовое направление)',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 31,
                'group' => 6,
                'name' => 'Уведомление о создании задачи Согласование КП (шпунтовое направление)',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 32,
                'group' => 6,
                'name' => 'Уведомление о создании задачи Согласование КП (свайное направление)',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 33,
                'group' => 6,
                'name' => 'Уведомление о создании задачи Согласование КП с заказчиком (шпунтовое направление)',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 34,
                'group' => 6,
                'name' => 'Уведомление о создании задачи Согласование КП с заказчиком (свайное направление)',
                'for_everyone' => 0, // for user
            ],
            [
                'id' => 35,
                'group' => 6,
                'name' => 'Уведомление о создании задачи Согласование КП с заказчиком (объединённое)',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 36,
                'group' => 6,
                'name' => 'Уведомление о завершении задачи Согласование КП',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 37,
                'group' => 6,
                'name' => 'Уведомление об обработке заявки на КП',
                'for_everyone' => 0, // for group and user
            ],
            // Contracts Notifications
            [
                'id' => 38,
                'group' => 7,
                'name' => 'Уведомление о создании задачи Формирование договоров',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 39,
                'group' => 7,
                'name' => 'Уведомление о создании задачи Формирование договора',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 40,
                'group' => 7,
                'name' => 'Уведомление о создании задачи Согласование договора',
                'for_everyone' => 1,
            ],
            [
                'id' => 41,
                'group' => 7,
                'name' => 'Уведомление о создании задачи Контроль подписания договора',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 42,
                'group' => 7,
                'name' => 'Уведомление о создании задачи Контроль подписания договора (повторно)',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 43,
                'group' => 7,
                'name' => 'Уведомление о создании задачи Контроль удаление договора',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 44,
                'group' => 7,
                'name' => 'Уведомление о решении задачи Запрос на удаление договора',
                'for_everyone' => 0, // for permission
            ],
            // Projects Notification
            [
                'id' => 45,
                'group' => 8,
                'name' => 'Уведомление о создании нового проекта',
                'for_everyone' => 0, // for group
            ],
            // Users Notification
            [
                'id' => 46,
                'group' => 9,
                'name' => 'Уведомление о замещении уходящего в отпуск пользователя',
                'for_everyone' => 1,
            ],
            [
                'id' => 47,
                'group' => 9,
                'name' => 'Уведомление о новых задачах от ушедшего в отпуск пользователя',
                'for_everyone' => 1,
            ],
            [
                'id' => 48,
                'group' => 9,
                'name' => 'Уведомление о выходе замещаемого пользователя из отпуска и передаче задач обратно',
                'for_everyone' => 1,
            ],
            [
                'id' => 49,
                'group' => 9,
                'name' => 'Уведомление о новых задачах от удаленного пользователя',
                'for_everyone' => 1,
            ],
            [
                'id' => 50,
                'group' => 7,
                'name' => 'Уведомление о создании задачи Контроль изменений коммерческого предложения',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 51,
                'group' => 7,
                'name' => 'Уведомление о создании задачи Контроль согласования договора',
                'for_everyone' => 0, // for group
            ],
            [
                'id' => 52,
                'group' => 1,
                'name' => 'Уведомление о создании стандартной задачи',
                'for_everyone' => 0, // for permission
            ],
        ]);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_types');
    }
};
