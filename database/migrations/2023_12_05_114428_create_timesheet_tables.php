<?php

use App\Models\Timesheet\Employees1cSalariesGroup;
use App\Models\Timesheet\ProductionCalendarDayType;
use App\Models\Timesheet\TimesheetDayCategory;
use App\Models\Timesheet\TimesheetState;
use App\Models\Timesheet\TimesheetTariff;
use App\Models\Timesheet\TimesheetTariffsType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (env('APP_ENV') == 'production') {
            return;
        }

        // Справочники
        if (! Schema::hasTable('timesheet_states')) {
            Schema::create('timesheet_states', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор записи');
                $table->string('name')->comment('Наименование состояния');

                $table->timestamps();
                $table->softDeletes();
            });
        }

        TimesheetState::insert([
            ['name' => 'Открыт', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Закрыт', 'created_at' => now(), 'updated_at' => now()],
        ]);

        if (! Schema::hasTable('production_calendar_day_types')) {
            Schema::create('production_calendar_day_types', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор типа дня в производственном календаре');
                $table->string('name')->comment('Наименование типа дня');

                $table->timestamps();
                $table->softDeletes();
            });
        }

        ProductionCalendarDayType::insert([
            ['name' => 'Выходной', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Перенесенный выходной', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Предпраздничный день', 'created_at' => now(), 'updated_at' => now()],
        ]);

        if (! Schema::hasTable('timesheet_tariffs_types')) {
            Schema::create('timesheet_tariffs_types', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор типа тарифа в табеле учета рабочего времени');
                $table->string('name')->comment('Наименование типа тарифа');
                $table->unsignedInteger('sort_order')->default(0)->comment('Порядок сортировки'); // Поле для управления порядком сортировки типов тарифов

                $table->timestamps();
                $table->softDeletes();
            });
        }

        TimesheetTariffsType::insert([
            ['name' => 'Часы', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Сделки', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Часы (штрафы)', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Часы (оклад)', 'created_at' => now(), 'updated_at' => now()],
        ]);

        if (! Schema::hasTable('timesheet_day_categories')) {
            Schema::create('timesheet_day_categories', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор категории дня в табеле учета рабочего времени');
                $table->string('name')->unique()->comment('Наименование категории дня');
                $table->string('shortname')->unique()->comment('Краткое наименование категории дня');

                $table->timestamps();
                $table->softDeletes();
            });
        }

        TimesheetDayCategory::insert([
            ['name' => 'Отпуск', 'shortname' => 'О', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Учебный отпуск', 'shortname' => 'У', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Больничный', 'shortname' => 'Б', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Отпуск без сохранения заработной платы', 'shortname' => 'З', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Прогул', 'shortname' => 'П', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Отсутствие по невыясненной причине', 'shortname' => 'Н', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Отпуск по беременности и родам', 'shortname' => 'БиР', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Отпуск по уходу за ребенком', 'shortname' => 'Д', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Мобилизация', 'shortname' => 'М', 'created_at' => now(), 'updated_at' => now()],
        ]);

        if (! Schema::hasTable('employees_1c_payments_deductions')) {
            Schema::create('employees_1c_payments_deductions', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор записи о платежах/вычетах сотрудника из 1С');
                $table->string('payments_deductions_1c_name')->comment('Наименование платежа/вычета из 1С');
                $table->string('synonym')->nullable()->comment('Синоним (псевдоним) для удобства использования');
                $table->boolean('use_in_export')->nullable()->comment('Флаг использования в процессе экспорта данных');

                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('employees_1c_salaries_groups')) {
            Schema::create('employees_1c_salaries_groups', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор группы зарплаты сотрудников из 1С');
                $table->string('name')->comment('Наименование группы зарплаты');

                $table->timestamps();
                $table->softDeletes();
            });
        }

        Employees1cSalariesGroup::insert([
            ['name' => 'Начислено', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Удержано', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Выплачено', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Данные
        if (! Schema::hasTable('production_calendars')) {
            Schema::create('production_calendars', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор записи в производственном календаре');
                $table->date('date')->comment('Дата в производственном календаре');
                $table->unsignedBigInteger('date_type')->comment('Тип дня');

                $table->timestamps();
                $table->softDeletes();

                $table->foreign('date_type')->references('id')->on('production_calendar_day_types');
            });
        }

        if (! Schema::hasTable('project_objects')) {
            Schema::table('project_objects', function (Blueprint $table) {
                $table->string('timesheet_shortname')->nullable()->after('short_name')
                    ->comment('Краткое наименование для использования в табеле учета рабочего времени');

                $table->boolean('is_business_trip')->index()->default(false)->after('is_participates_in_documents_flow')
                    ->comment('Флаг, указывающий, является ли объект проекта командировочным');
            });
        }

        if (! Schema::hasTable('timesheet_cards')) {
            Schema::create('timesheet_cards', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор табеля учета рабочего времени');
                $table->unsignedBigInteger('employee_id')->comment('Идентификатор сотрудника, для которого ведется табель');
                $table->unsignedInteger('month')->index()->comment('Месяц табеля, с индексированием для ускорения поиска');
                $table->unsignedInteger('year')->index()->comment('Год табеля, с индексированием для ускорения поиска');
                $table->unsignedBigInteger('timesheet_state_id')->default(1)->comment('Идентификатор состояния табеля');
                $table->decimal('ktu', 8, 2)->unsigned()->default(0)->comment('Коэффициент трудоемкости (KTU)');

                $table->audit();

                $table->foreign('employee_id')->references('id')->on('employees');
                $table->foreign('timesheet_state_id')->references('id')->on('timesheet_states');
            });
        }

        if (! Schema::hasTable('timesheet_project_objects_bonuses')) {
            Schema::create('timesheet_project_objects_bonuses', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор бонуса для объекта проекта в табеле учета рабочего времени');
                $table->unsignedBigInteger('timesheet_card_id')->comment('Идентификатор табеля учета рабочего времени');
                $table->unsignedInteger('project_object_id')->comment('Идентификатор объекта проекта');
                $table->string('name')->nullable()->comment('Наименование бонуса');
                $table->double('value', 8, 2)->comment('Значение бонуса');

                $table->audit();

                $table->foreign('timesheet_card_id')->references('id')->on('timesheet_cards');
                $table->foreign('project_object_id')->references('id')->on('project_objects');
            });
        }

        if (! Schema::hasTable('employees_1c_salaries')) {
            Schema::create('employees_1c_salaries', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор записи о зарплате сотрудника из 1С');
                $table->unsignedBigInteger('employee_id')->comment('Идентификатор сотрудника, для которого указана зарплата');
                $table->unsignedBigInteger('employees_1c_salaries_group_id')->comment('Идентификатор группы зарплаты из 1С');
                $table->unsignedInteger('month')->index()->comment('Месяц, за который указана зарплата');
                $table->unsignedInteger('year')->index()->comment('Год, за который указана зарплата');
                $table->string('name')->comment('Наименование зарплаты');
                $table->float('value')->comment('Значение зарплаты');

                $table->timestamps();
                $table->softDeletes();

                $table->foreign('employee_id')->references('id')->on('employees');
                $table->foreign('employees_1c_salaries_group_id')->references('id')->on('employees_1c_salaries_groups');
            });
        }

        if (! Schema::hasTable('timesheet_post_tariffs')) {
            Schema::create('timesheet_post_tariffs', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор тарифа по часам');
                $table->unsignedInteger('post_id')->comment('Идентификатор должности');
                $table->date('tariff_start_date')->index()->comment('Дата начала действия тарифа');
                $table->date('tariff_end_date')->index()->nullable()->default(null)->comment('Дата окончания действия тарифа');

                $table->timestamps();
                $table->softDeletes();

                $table->foreign('post_id')->references('id')->on('groups');
            });
        }

        if (! Schema::hasTable('timesheet_tariffs')) {
            Schema::create('timesheet_tariffs', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор тарифа по часам');
                $table->string('name')->comment('Наименование тарифа');
                $table->unsignedBigInteger('timesheet_tariffs_type_id')->comment('Тип тарифа');
                $table->boolean('is_overwork')->default(0)->comment('Является ли тариф переработкой');
                $table->string('tariff_color')->nullable()->comment('Цвет тарифа');
                $table->unsignedInteger('sort_order')->comment('Порядок сортировки тарифа');

                $table->timestamps();
                $table->softDeletes();

                $table->foreign('timesheet_tariffs_type_id')->references('id')->on('timesheet_tariffs_types');
            });
        }

        TimesheetTariff::insert([
            ['name' => 'Обычный час', 'timesheet_tariffs_type_id' => 1, 'is_overwork' => false, 'tariff_color' => '', 'sort_order' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Переработки', 'timesheet_tariffs_type_id' => 1, 'is_overwork' => true, 'tariff_color' => '', 'sort_order' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Монтаж крепления', 'timesheet_tariffs_type_id' => 1, 'is_overwork' => false, 'tariff_color' => '', 'sort_order' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Монтаж крепления (Переработка)', 'timesheet_tariffs_type_id' => 1, 'is_overwork' => true, 'tariff_color' => '', 'sort_order' => 40, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Демонтаж крепления', 'timesheet_tariffs_type_id' => 1, 'is_overwork' => false, 'tariff_color' => '', 'sort_order' => 50, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Демонтаж крепления (Переработка)', 'timesheet_tariffs_type_id' => 1, 'is_overwork' => true, 'tariff_color' => '', 'sort_order' => 60, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Обычный час с г/м', 'timesheet_tariffs_type_id' => 1, 'is_overwork' => false, 'tariff_color' => '', 'sort_order' => 70, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Переработки с г/м', 'timesheet_tariffs_type_id' => 1, 'is_overwork' => true, 'tariff_color' => '', 'sort_order' => 80, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Zoomlion', 'timesheet_tariffs_type_id' => 1, 'is_overwork' => false, 'tariff_color' => '', 'sort_order' => 90, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Zoomlion (переработка)', 'timesheet_tariffs_type_id' => 1, 'is_overwork' => true, 'tariff_color' => '', 'sort_order' => 100, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Вспомогательные работы', 'timesheet_tariffs_type_id' => 1, 'is_overwork' => false, 'tariff_color' => '', 'sort_order' => 110, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Простой (дома)', 'timesheet_tariffs_type_id' => 1, 'is_overwork' => true, 'tariff_color' => '', 'sort_order' => 120, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Опоздание', 'timesheet_tariffs_type_id' => 3, 'is_overwork' => false, 'tariff_color' => '', 'sort_order' => 130, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Погружение вибро', 'timesheet_tariffs_type_id' => 2, 'is_overwork' => false, 'tariff_color' => '#FF6666', 'sort_order' => 140, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Погружение вдвоем вибро', 'timesheet_tariffs_type_id' => 2, 'is_overwork' => false, 'tariff_color' => '#FFA0A0', 'sort_order' => 150, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Извлечение вибро', 'timesheet_tariffs_type_id' => 2, 'is_overwork' => false, 'tariff_color' => '#E6B9B8', 'sort_order' => 160, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Погружение статика', 'timesheet_tariffs_type_id' => 2, 'is_overwork' => false, 'tariff_color' => '#0070C0', 'sort_order' => 170, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Извлечение статика', 'timesheet_tariffs_type_id' => 2, 'is_overwork' => false, 'tariff_color' => '#8DB4E3', 'sort_order' => 180, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Обычный час', 'timesheet_tariffs_type_id' => 4, 'is_overwork' => false, 'tariff_color' => '', 'sort_order' => 190, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Переработки', 'timesheet_tariffs_type_id' => 4, 'is_overwork' => true, 'tariff_color' => '', 'sort_order' => 200, 'created_at' => now(), 'updated_at' => now()],
        ]);

        if (! Schema::hasTable('timesheet_tariff_rates')) {
            Schema::create('timesheet_tariff_rates', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор тарифа по часам');
                $table->unsignedBigInteger('timesheet_tariff_id')->comment('Тариф');
                $table->unsignedBigInteger('timesheet_post_tariff_id')->comment('Тариф должности');

                $table->double('rate', 8, 2)->comment('Сумма тарифа');

                $table->timestamps();
                $table->softDeletes();

                $table->foreign('timesheet_tariff_id')->references('id')->on('timesheet_tariffs');
                $table->foreign('timesheet_post_tariff_id')->references('id')->on('timesheet_post_tariffs');
            });
        }

        if (! Schema::hasTable('timesheet_aggregated_salary_summary')) {
            Schema::create('timesheet_aggregated_salary_summary', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор сводной информации по зарплате в табеле учета рабочего времени');
                $table->unsignedBigInteger('timesheet_card_id')->comment('Идентификатор табеля учета рабочего времени');
                $table->unsignedBigInteger('employee_id')->comment('Идентификатор сотрудника');
                $table->unsignedInteger('project_object_id')->nullable()->comment('Идентификатор объекта проекта (если применимо)');
                $table->unsignedInteger('post_id')->comment('Идентификатор должности сотрудника');
                $table->date('date')->comment('Дата сводной информации');
                $table->unsignedBigInteger('timesheet_tariffs_type_id')->comment('Идентификатор типа тарифа');
                $table->unsignedBigInteger('timesheet_post_tariff_id')->comment('Идентификатор тарифа');
                $table->float('rate')->comment('Ставка');
                $table->integer('count')->comment('Количество (например, количество часов)');
                $table->float('summary_salary')->comment('Суммарная зарплата');

                $table->timestamps();
                $table->softDeletes();

                $table->foreign('timesheet_card_id')->references('id')->on('timesheet_cards');
                $table->foreign('employee_id')->references('id')->on('employees');
                $table->foreign('project_object_id')->references('id')->on('project_objects');
                $table->foreign('post_id')->references('id')->on('groups');
                $table->foreign('timesheet_tariffs_type_id', 'summary_timesheet_tariffs_type_id_foreign')->references('id')->on('timesheet_tariffs_types');
                $table->foreign('timesheet_post_tariff_id', 'summary_timesheet_post_tariff_id_foreign')->references('id')->on('timesheet_post_tariffs');
            });
        }

        if (! Schema::hasTable('timesheet_employees_objects')) {
            Schema::create('timesheet_employees_objects', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор связи между сотрудником и объектом проекта в табеле учета рабочего времени');
                $table->unsignedInteger('project_object_id')->comment('Идентификатор объекта проекта');
                $table->unsignedBigInteger('employee_id')->comment('Идентификатор сотрудника');
                $table->date('date')->index()->comment('Дата связи между сотрудником и объектом проекта');

                $table->audit();

                $table->foreign('project_object_id')->references('id')->on('project_objects');
                $table->foreign('employee_id')->references('id')->on('employees');
            });
        }

        if (! Schema::hasTable('timesheet_employees_compensations')) {
            Schema::create('timesheet_employees_compensations', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор компенсации сотрудника');
                $table->unsignedBigInteger('timesheet_card_id')->comment('Идентификатор табеля учета рабочего времени');
                $table->unsignedInteger('compensation_type')->default(1)->comment('Тип компенсации: 1 - введенная вручную, 2 - сгенерирована автоматически');
                $table->unsignedInteger('compensation_value')->comment('Значение компенсации');
                $table->string('compensation_comment', 256)->comment('Комментарий к компенсации');
                $table->tinyInteger('prolongation')->default(0)->comment('Флаг пролонгации');
                $table->unsignedBigInteger('prolongation_compensation_id')->nullable()->comment('ID пролонгированной записи');

                $table->audit();

                $table->index('prolongation_compensation_id', 'prolongation_compensation_id_index');

                $table->foreign('timesheet_card_id')->references('id')->on('timesheet_cards');
            });
        }

        if (! Schema::hasTable('timesheet_employees_penalties')) {
            Schema::create('timesheet_employees_penalties', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор штрафа сотрудника');
                $table->unsignedBigInteger('timesheet_card_id')->comment('Идентификатор табеля учета рабочего времени');
                $table->unsignedInteger('penalty_value')->comment('Значение штрафа');
                $table->string('penalty_comment')->nullable()->comment('Комментарий к штрафу');

                $table->audit();

                $table->foreign('timesheet_card_id')->references('id')->on('timesheet_cards');
            });
        }

        if (! Schema::hasTable('timesheet_employees_summary_hours')) {
            Schema::create('timesheet_employees_summary_hours', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор сводных часов сотрудника');
                $table->unsignedBigInteger('timesheet_card_id')->comment('Идентификатор табеля учета рабочего времени');
                $table->unsignedBigInteger('timesheet_day_category_id')->nullable()->comment('Тип часов (например, отработанные, отпускные, больничные)');
                $table->date('date')->index()->comment('Дата сводных часов');
                $table->unsignedInteger('count')->comment('Количество часов');

                $table->audit();

                $table->foreign('timesheet_day_category_id', 'summary_hours_timesheet_date_category_id_foreign')->references('id')->on('timesheet_day_categories');
                $table->foreign('timesheet_card_id')->references('id')->on('timesheet_cards');
            });
        }

        if (! Schema::hasTable('timesheet')) {
            Schema::create('timesheet', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Уникальный идентификатор записи в табеле');
                $table->unsignedBigInteger('timesheet_card_id')->comment('Идентификатор табеля учета рабочего времени');
                $table->date('date')->index()->comment('Дата записи в табеле');
                $table->float('deal_multiplier')->nullable()->comment('Множитель для сделок (если применяется)');
                $table->unsignedInteger('count')->comment('Количество (количество часов или метров (для сделок))');

                $table->audit();

                $table->foreign('timesheet_card_id')->references('id')->on('timesheet_cards');
            });
        }

        DB::statement("ALTER TABLE timesheet_states COMMENT 'Справочник состояний табеля'");
        DB::statement("ALTER TABLE production_calendar_day_types COMMENT 'Таблица типов дней в производственном календаре'");
        DB::statement("ALTER TABLE timesheet_tariffs_types COMMENT 'Таблица типов тарифов в табеле учета рабочего времени'");
        DB::statement("ALTER TABLE timesheet_day_categories COMMENT 'Таблица категорий дней в табеле учета рабочего времени'");
        DB::statement("ALTER TABLE employees_1c_payments_deductions COMMENT 'Таблица платежей/вычетов сотрудников из 1С'");
        DB::statement("ALTER TABLE employees_1c_salaries_groups COMMENT 'Таблица групп зарплаты сотрудников из 1С'");
        DB::statement("ALTER TABLE production_calendars COMMENT 'Таблица производственного календаря'");
        DB::statement("ALTER TABLE timesheet_cards COMMENT 'Таблица табелей учета рабочего времени'");
        DB::statement("ALTER TABLE timesheet_project_objects_bonuses COMMENT 'Таблица бонусов для объектов проектов в табеле учета рабочего времени'");
        DB::statement("ALTER TABLE employees_1c_salaries COMMENT 'Таблица зарплат сотрудников из 1С'");
        DB::statement("ALTER TABLE timesheet_aggregated_salary_summary COMMENT 'Таблица сводной информации по зарплате в табеле учета рабочего времени'");
        DB::statement("ALTER TABLE timesheet_employees_objects COMMENT 'Таблица связей между сотрудниками и объектами проектов в табеле учета рабочего времени'");
        DB::statement("ALTER TABLE timesheet_employees_compensations COMMENT 'Таблица компенсаций сотрудников'");
        DB::statement("ALTER TABLE timesheet_employees_penalties COMMENT 'Таблица штрафов сотрудников'");
        DB::statement("ALTER TABLE timesheet_employees_summary_hours COMMENT 'Таблица сводных часов сотрудника'");
        DB::statement("ALTER TABLE timesheet COMMENT 'Таблица записей в табеле учета рабочего времени'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet'); // Timesheet
        Schema::dropIfExists('timesheet_employees_summary_hours'); //TimesheetEmployeesSummaryHour
        Schema::dropIfExists('timesheet_employees_penalties'); //TimesheetEmployeesPenalty
        Schema::dropIfExists('timesheet_employees_compensations'); //TimesheetEmployeesCompensation
        Schema::dropIfExists('timesheet_employees_objects'); //TimesheetEmployeesObject
        Schema::dropIfExists('timesheet_aggregated_salary_summary'); //TimesheetAggregatedSalarySummary
        Schema::dropIfExists('timesheet_tariff_rates'); //TimesheetTariffRate
        Schema::dropIfExists('timesheet_tariffs'); //TimesheetTariff
        Schema::dropIfExists('timesheet_post_tariffs'); //TimesheetPostTariff
        Schema::dropIfExists('employees_1c_salaries'); //Employees1cSalary
        Schema::dropIfExists('timesheet_project_objects_bonuses'); //TimesheetProjectObjectsBonus
        Schema::dropIfExists('timesheet_cards'); //TimesheetCard
        Schema::dropIfExists('production_calendars'); //ProductionCalendar
        Schema::dropIfExists('employees_1c_salaries_groups'); //Employees1cSalariesGroup
        Schema::dropIfExists('employees_1c_payments_deductions'); //Employees1cPaymentDeduction
        Schema::dropIfExists('timesheet_day_categories'); //TimesheetDayCategory
        Schema::dropIfExists('timesheet_tariffs_types'); //TimesheetTariffsType
        Schema::dropIfExists('production_calendar_day_types'); //ProductionCalendarDayType
        Schema::dropIfExists('timesheet_states'); //TimesheetState

        // TUCKI old tables
        Schema::dropIfExists('timecard_additions');
        Schema::dropIfExists('timecard_days');
        Schema::dropIfExists('timecard_records');
        Schema::dropIfExists('timecards');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('brigades');
        Schema::dropIfExists('job_categories');
        Schema::dropIfExists('job_category_tariffs');
        Schema::dropIfExists('pay_and_holds');
        Schema::dropIfExists('report_groups');
        Schema::dropIfExists('tariff_rates');

        Schema::table('project_objects', function (Blueprint $table) {
            if (Schema::hasColumn('timesheet_shortname', 'project_objects')) {
                $table->dropColumn('timesheet_shortname');
            }

            if (Schema::hasColumn('is_business_trip', 'project_objects')) {
                $table->dropColumn('is_business_trip');
            }
        });
    }
};
