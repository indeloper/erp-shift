<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimesheetTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    // Права на табель
    public function up()
    {
        // Справочники
        Schema::create('timesheet_states', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('state_name');

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE timesheet_states COMMENT 'Справочник состояний табеля'");

        Schema::create('production_calendar_day_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('timesheet_tariffs_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0)->comment('Порядок сортировки');

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE timesheet_tariffs_types COMMENT 'Справочник типов тарифов'");

        //old name: hour_types
        Schema::create('timesheet_day_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('shortname');

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE timesheet_day_categories COMMENT 'Справочник категорий рабочего времени'");

        Schema::create('employees_1c_payments_deductions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('payments_deductions_1c_name');
            $table->string('synonym')->nullable();
            $table->boolean('use_in_export')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employees_1c_salaries_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
        });

        // Данные
        Schema::create('production_calendar', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date');
            $table->unsignedBigInteger('date_type')->nullable()->comment('Тип дня (null - обычный день)');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('project_objects', function (Blueprint $table) {
            $table->string('timesheet_shortname')->nullable()->after('short_name');
            $table->boolean('is_business_trip')->default(false)->after('is_participates_in_documents_flow');
        });

        Schema::create('timesheet_card', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedInteger('month')->index();
            $table->unsignedInteger('year')->index();
            $table->unsignedInteger('timesheet_state_id');
            $table->unsignedInteger('ktu');

            $table->unsignedInteger('author_id');
            $table->unsignedInteger('editor_id');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('timesheet_project_objects_bonuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('timesheet_card_id');
            $table->unsignedInteger('project_object_id');
            $table->string('name')->nullable();
            $table->double('value', 8,2);

            $table->unsignedInteger('author_id');
            $table->unsignedInteger('editor_id');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employees_1c_salaries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('salary_group_id');
            $table->unsignedInteger('month');
            $table->unsignedInteger('year');
            $table->string('name');
            $table->float('value');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('timesheet_aggregated_salary_summary', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('timesheet_card_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('project_object_id')->nullable();
            $table->unsignedBigInteger('post_id');
            $table->date('date');
            $table->unsignedBigInteger('tariff_type');
            $table->unsignedBigInteger('tariff_id');
            $table->float('rate');
            $table->integer('count');
            $table->float('summary_salary');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('timesheet_employees_objects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('project_object_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('date');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employees_compensation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('timesheet_card_id');
            $table->unsignedInteger('compensation_type')->default(1)->comment('1 - введенная вручную, 2 - сгенерирована автоматически');
            $table->unsignedInteger('compensation_value');
            $table->string('compensation_comment', 256);
            $table->tinyInteger('prolongation')->default(0)->comment('Пролонгация');
            $table->unsignedInteger('prolongation_compensation_id')->nullable()->comment('ID пролонгированной записи');

            $table->timestamps();
            $table->softDeletes();
        });

        // Не обработано







        Schema::create('employees_ktu', function (Blueprint $table) {
            $table->id('employees_ktu_id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('month');
            $table->unsignedInteger('year');
            $table->float('ktu_value');
            $table->unsignedInteger('edited_user_id');
            $table->dateTime('edited_date')->default(now())->nullable(false)->useCurrentOnUpdate();
        });

        Schema::create('employees_penalties', function (Blueprint $table) {
            $table->id('penalty_id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('month');
            $table->unsignedInteger('year');
            $table->unsignedInteger('penalty_value');
            $table->string('penalty_comment', 256)->nullable();
            $table->dateTime('edited_date')->default(now())->nullable(false)->useCurrentOnUpdate();
            $table->unsignedInteger('edited_user_id');
        });

        Schema::create('employees_salaries', function (Blueprint $table) {
            $table->id('salary_id');
            $table->unsignedInteger('post_id');
            $table->date('salary_start_date');
            $table->float('salary_value');
            $table->tinyInteger('deleted')->default(0);
        });

        Schema::create('employees_summary_hours', function (Blueprint $table) {
            $table->id('employees_summary_hours_id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('hour_type');
            $table->unsignedInteger('count');
            $table->date('summary_hour_date');
            $table->unsignedInteger('edited_user_id');
            $table->dateTime('edited_date')->default('0000-00-00 00:00:00')->nullable(false)->useCurrentOnUpdate();
        });

        Schema::create('posts_rates', function (Blueprint $table) {
            $table->id('post_rates_id');
            $table->unsignedInteger('post_id');
            $table->unsignedInteger('tariff_id');
            $table->float('tariff_rate');
            $table->tinyInteger('deleted')->default(0);
        });

        Schema::create('tariffs', function (Blueprint $table) {
            $table->id('tariff_id');
            $table->string('tariff_name', 150);
            $table->unsignedInteger('tariff_type');
            $table->string('tariff_color', 16)->nullable()->comment('Цвет тарифа');
            $table->unsignedInteger('sort_order');
        });

        Schema::create('timesheet', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('post_id');
            $table->unsignedInteger('tariff_id');
            $table->float('rate')->nullable();
            $table->float('deal_multiplier')->nullable();
            $table->date('timesheet_date');
            $table->unsignedInteger('count');
            $table->unsignedInteger('edited_user_id');
            $table->dateTime('edited_date')->default('0000-00-00 00:00:00')->nullable(false)->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
