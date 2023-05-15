<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQ3wMaterialSupplyObjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('q3w_material_supply_objects', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->string('name')->comment('Наименование объекта для планирования поставок');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('q3w_material_supply_planning', function (Blueprint $table) {
            $table->bigInteger('planning_object_id')->unsigned()->after('id')->comment('Идентификатор планируемого объекта');
            $table->foreign('planning_object_id')->references('id')->on('q3w_material_supply_objects');

            $table->dropForeign('q3w_material_supply_planning_project_object_id_foreign');

            $table->dropColumn('project_object_id');
            $table->dropColumn('amount');
        });

        Schema::table('q3w_material_supply_materials', function (Blueprint $table) {
            $table->integer('source_project_object_id')->unsigned()->comment('Объект, с которого планируется поставка')->after('supply_planning_id');
            $table->double('weight')->unsigned()->comment('Планируемый вес завоза')->after('source_project_object_id');

            $table->foreign('source_project_object_id')->references('id')->on('project_objects');

            $table->dropColumn('amount');

            $table->dropForeign('q3w_material_supply_materials_material_id_foreign');
            $table->dropColumn('material_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('q3w_material_supply_planning', function (Blueprint $table) {
            $table->dropForeign('q3w_material_supply_planning_planning_object_id_foreign');
            $table->dropColumn('planning_object_id');

            $table->integer('project_object_id')->unsigned()->comment('Идентификатор объекта');
            $table->foreign('project_object_id')->references('id')->on('projects');

            $table->integer('amount')->unsigned()->comment('Количество в штуках')->after('quantity');
        });

        Schema::table('q3w_material_supply_materials', function (Blueprint $table) {
            $table->dropForeign('q3w_material_supply_materials_supply_planning_id_foreign');
            $table->dropColumn('source_project_object_id');

            $table->dropColumn('weight');

            $table->bigInteger('material_id')->unsigned()->comment('Выбранный материал');
            $table->foreign('material_id')->references('id')->on('q3w_materials');

            $table->integer('amount')->unsigned()->comment('Количество выбранного материала');
        });


        Schema::dropIfExists('q3w_material_supply_objects');
    }
}
