<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionsForUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->unsignedInteger('category');
        });

        DB::table('permissions')->insert([
            // tasks
            [
                'category' => 1,
                "name" => 'Просмотр "дашборда"',
                "codename" => 'dashbord',
            ],
            [
                'category' => 1,
                "name" => 'Просмотр списка задач',
                "codename" => 'tasks',
            ],
            [
                'category' => 1,
                "name" => 'Создание свободной задачи (себе)',
                "codename" => 'tasks_default_myself',
            ],
            [
                'category' => 1,
                "name" => 'Создание свободной задачи (другим)',
                "codename" => 'tasks_default_others',
            ],
            [
                'category' => 2,
                "name" => 'Просмотр базы проектов',
                "codename" => 'projects',
            ],
            [
                'category' => 2,
                "name" => 'Добавление проекта',
                "codename" => 'projects_create',
            ],
            [
                'category' => 2,
                "name" => 'Изменение списка отв. лиц проекта',
                "codename" => 'projects_responsible_users',
            ],
            [
                'category' => 3,
                "name" => 'Просмотр базы контрагентов',
                "codename" => 'contractors',
            ],
            [
                'category' => 3,
                "name" => 'Добавление контрагента',
                "codename" => 'contractors_create',
            ],
            [
                'category' => 3,
                "name" => 'Редактирование контрагента',
                "codename" => 'contractors_edit',
            ],
            [
                'category' => 3,
                "name" => 'Изменение списка контактов контрагента',
                "codename" => 'contractors_contacts',
            ],
            [
                'category' => 3,
                "name" => 'Удаление контрагента',
                "codename" => 'contractors_delete',
            ],
            [
                'category' => 4,
                "name" => 'Просмотр всех объектов',
                "codename" => 'objects',
            ],
            [
                'category' => 4,
                "name" => 'Добавление объекта',
                "codename" => 'objects_create',
            ],
            [
                'category' => 4,
                "name" => 'Изменение данных объекта',
                "codename" => 'objects_edit',
            ],
            [
                'category' => 5,
                "name" => 'Просмотр справочника материалов',
                "codename" => 'manual_materials',
            ],
            [
                'category' => 5,
                "name" => 'Изменение справочника материалов',
                "codename" => 'manual_materials_edit',
            ],
            [
                'category' => 5,
                "name" => 'Просмотр справочника типовых узлов',
                "codename" => 'manual_nodes',
            ],
            [
                'category' => 5,
                "name" => 'Изменение справочника типовых узлов',
                "codename" => 'manual_nodes_edit',
            ],
            [
                'category' => 6,
                "name" => 'Просмотр справочника работ',
                "codename" => 'manual_works',
            ],
            [
                'category' => 6,
                "name" => 'Изменение справочника работ',
                "codename" => 'manual_works_edit',
            ],
            [
                'category' => 7,
                "name" => 'Просмотр "Табеля материального учёта"',
                "codename" => 'mat_acc_report_card',
            ],
            [
                'category' => 7,
                "name" => 'Просмотр "Журнала операций"',
                "codename" => 'mat_acc_operation_log',
            ],
            [
                'category' => 7,
                "name" => 'Создание операции "Перемещение"',
                "codename" => 'mat_acc_moving_create',
            ],
            [
                'category' => 7,
                "name" => 'Создание черновика операции "Перемещение"',
                "codename" => 'mat_acc_moving_draft_create',
            ],
            [
                'category' => 7,
                "name" => 'Создание операции "Списание"',
                "codename" => 'mat_acc_write_off_create',
            ],
            [
                'category' => 7,
                "name" => 'Создание черновика операции "Списание"',
                "codename" => 'mat_acc_write_off_draft_create',
            ],
            [
                'category' => 7,
                "name" => 'Создание операции "Преобразование"',
                "codename" => 'mat_acc_transformation_create',
            ],
            [
                'category' => 7,
                "name" => 'Создание черновика операции "Преобразование"',
                "codename" => 'mat_acc_transformation_draft_create',
            ],
            [
                'category' => 7,
                "name" => 'Создание операции "Поступления"',
                "codename" => 'mat_acc_arrival_create',
            ],
            [
                'category' => 7,
                "name" => 'Создание черновика операции "Поступления"',
                "codename" => 'mat_acc_arrival_draft_create',
            ],
            [
                'category' => 8,
                "name" => 'Просмотр базы проектной документации',
                "codename" => 'project_documents',
            ],
            [
                'category' => 9,
                "name" => 'Просмотр базы коммерческих предложений',
                "codename" => 'commercial_offers',
            ],
            [
                'category' => 10,
                "name" => 'Просмотр базы договоров',
                "codename" => 'contracts',
            ],
            [
                'category' => 10,
                "name" => 'Добавление договора',
                "codename" => 'contracts_create',
            ],
            [
                'category' => 10,
                "name" => 'Запрос на удаление договора',
                "codename" => 'contracts_delete_request',
            ],
            [
                'category' => 11,
                "name" => 'Просмотр базы объёмов работ',
                "codename" => 'work_volumes',
            ],
            [
                'category' => 12,
                "name" => 'Просмотр базы сотрудников',
                "codename" => 'users',
            ],
            [
                'category' => 12,
                "name" => 'Добавление сотрудника',
                "codename" => 'users_create',
            ],
            [
                'category' => 12,
                "name" => 'Изменение сотрудника',
                "codename" => 'users_edit',
            ],
            [
                'category' => 12,
                "name" => 'Удаление сотрудника',
                "codename" => 'users_delete',
            ],
            [
                'category' => 12,
                "name" => 'Изменение прав Подразделения/Должности/Пользователя',
                "codename" => 'users_permissions',
            ],
            [
                'category' => 12,
                "name" => 'Отпуск сотрудника',
                "codename" => 'users_vacations',
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('permissions')->where("codename", 'dashbord')->delete();
        DB::table('permissions')->where("codename", 'tasks')->delete();
        DB::table('permissions')->where("codename", 'tasks_default_myself')->delete();
        DB::table('permissions')->where("codename", 'tasks_default_others')->delete();

        DB::table('permissions')->where("codename", 'projects')->delete();
        DB::table('permissions')->where("codename", 'projects_create')->delete();
        DB::table('permissions')->where("codename", 'projects_responsible_users')->delete();

        DB::table('permissions')->where("codename", 'contractors')->delete();
        DB::table('permissions')->where("codename", 'contractors_create')->delete();
        DB::table('permissions')->where("codename", 'contractors_edit')->delete();
        DB::table('permissions')->where("codename", 'contractors_contacts')->delete();
        DB::table('permissions')->where("codename", 'contractors_delete')->delete();

        DB::table('permissions')->where("codename", 'objects')->delete();
        DB::table('permissions')->where("codename", 'objects_create')->delete();
        DB::table('permissions')->where("codename", 'objects_edit')->delete();

        DB::table('permissions')->where("codename", 'manual_materials')->delete();
        DB::table('permissions')->where("codename", 'manual_materials_edit')->delete();
        DB::table('permissions')->where("codename", 'manual_nodes')->delete();
        DB::table('permissions')->where("codename", 'manual_nodes_edit')->delete();

        DB::table('permissions')->where("codename", 'manual_works')->delete();
        DB::table('permissions')->where("codename", 'manual_works_edit')->delete();

        DB::table('permissions')->where("codename", 'mat_acc_report_card')->delete();
        DB::table('permissions')->where("codename", 'mat_acc_operation_log')->delete();
        DB::table('permissions')->where("codename", 'mat_acc_moving_create')->delete();
        DB::table('permissions')->where("codename", 'mat_acc_write_off_create')->delete();
        DB::table('permissions')->where("codename", 'mat_acc_transformation_create')->delete();
        DB::table('permissions')->where("codename", 'mat_acc_arrival_create')->delete();

        DB::table('permissions')->where("codename", 'mat_acc_moving_draft_create')->delete();
        DB::table('permissions')->where("codename", 'mat_acc_write_off_draft_create')->delete();
        DB::table('permissions')->where("codename", 'mat_acc_transformation_off_draft_create')->delete();
        DB::table('permissions')->where("codename", 'mat_acc_arrival_draft_create')->delete();

        DB::table('permissions')->where("codename", 'project_documents')->delete();

        DB::table('permissions')->where("codename", 'commercial_offers')->delete();

        DB::table('permissions')->where("codename", 'contracts')->delete();
        DB::table('permissions')->where("codename", 'contracts_create')->delete();
        DB::table('permissions')->where("codename", 'contracts_delete_request')->delete();

        DB::table('permissions')->where("codename", 'work_volumes')->delete();

        DB::table('permissions')->where("codename", 'users')->delete();
        DB::table('permissions')->where("codename", 'users_create')->delete();
        DB::table('permissions')->where("codename", 'users_edit')->delete();
        DB::table('permissions')->where("codename", 'users_delete')->delete();
        DB::table('permissions')->where("codename", 'users_permissions')->delete();
        DB::table('permissions')->where("codename", 'users_vacations')->delete();

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
}
