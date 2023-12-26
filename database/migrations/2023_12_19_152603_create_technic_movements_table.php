<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTechnicMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technic_movements', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('technic_movement_status_id')->unsigned()->comment('ID статус перемещения техники');
            $table->foreign('technic_movement_status_id')->references('id')->on('technic_movement_statuses');

            $table->bigInteger('technic_category_id')->nullable()->unsigned()->comment('ID категории техники');
            $table->foreign('technic_category_id')->references('id')->on('technic_categories');

            $table->bigInteger('technic_id')->nullable()->unsigned()->comment('ID единицы техники');
            $table->foreign('technic_id')->references('id')->on('our_technics');

            $table->date('order_start_date')->nullable();
            $table->date('order_end_date')->nullable();
            $table->string('order_comment')->nullable();
            $table->dateTime('movement_start_datetime')->nullable();

            $table->unsignedInteger('contractor_id')->nullable()->comment('ID контрагента перевозчика');
            $table->foreign('contractor_id')->references('id')->on('contractors');

            $table->unsignedInteger('responsible_id')->comment('ID отвественного пользователя');
            $table->foreign('responsible_id')->references('id')->on('users');

            $table->unsignedInteger('previous_responsible_id')->nullable()->comment('ID предыдущего отвественного пользователя');
            $table->foreign('previous_responsible_id')->references('id')->on('users');

            $table->unsignedInteger('object_id')->comment('ID объекта прибытия');
            $table->foreign('object_id')->references('id')->on('project_objects');

            $table->unsignedInteger('previous_object_id')->nullable()->comment('ID объекта убытия');
            $table->foreign('previous_object_id')->references('id')->on('project_objects');

            $table->audit();
        });

        DB::statement("ALTER TABLE technic_movements COMMENT 'Журнал перемещений техники»'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('technic_movements', function (Blueprint $table) {

        });

        Schema::dropIfExists('technic_movements');
    }
}
