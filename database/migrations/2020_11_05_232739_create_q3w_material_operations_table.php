<?php

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
        Schema::create('q3w_material_operation_types', function (Blueprint $table) {
            $table->integerIncrements('id')->comment('Уникальный идентификатор');
            $table->string('name')->comment('Наименование типа операции');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('q3w_material_operations', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->integer('operation_type_id')->unsigned()->index()->comment('Идентификатор типа операции');

            $table->integer('source_project_object_id')->unsigned()->nullable()->index()->comment('Идентификатор объекта, с которого отправляется материал');
            $table->integer('destination_project_object_id')->unsigned()->nullable()->index()->comment('Идентификатор идентификатор объекта, куда должен прибыть материал');

            $table->integer('contractor_id')->unsigned()->index()->comment('Идентификатор контрагента (поставщика)');
            //Договор $table->time('contractor_id')->unsigned()->nullable()->index()->comment('Идентификатор контрагента (поставщика)');

            $table->timestamp('planned_date_start')->comment('Плановая дата начала поставки');
            $table->timestamp('planned_date_end')->nullable()->comment('Плановая дата окончания поставки');

            $table->integer('creator_user_id')->unsigned()->index()->comment('ID пользователя, создавшего операцию');
            $table->integer('responsible_user_id')->unsigned()->index()->comment('ID ответственного пользователя');

            $table->text('creator_comment')->comment('Комментарий пользователя');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('operation_type_id')->references('id')->on('q3w_material_operation_types');
            $table->foreign('source_project_object_id')->references('id')->on('project_objects');
            $table->foreign('destination_project_object_id')->references('id')->on('project_objects');
            $table->foreign('contractor_id')->references('id')->on('contractors');
            $table->foreign('creator_user_id')->references('id')->on('users');
            $table->foreign('responsible_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('q3w_material_operations');
        Schema::dropIfExists('q3w_material_operation_types');
    }
}
