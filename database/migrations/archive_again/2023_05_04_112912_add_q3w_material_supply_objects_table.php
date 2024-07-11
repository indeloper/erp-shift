<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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

            $table->integer('brand_type_id')->unsigned()->after('id')->comment('Тип марки');
            $table->foreign('brand_type_id')->references('id')->on('q3w_material_brand_types');

            $table->dropForeign('q3w_material_supply_planning_project_object_id_foreign');
            $table->dropColumn('project_object_id');

            $table->dropForeign('q3w_material_supply_planning_brand_id_foreign');
            $table->dropColumn('brand_id');

            $table->dropColumn('amount');

            $table->decimal('quantity', 8, 3)->change();
        });

        Schema::table('q3w_material_supply_materials', function (Blueprint $table) {
            $table->integer('source_project_object_id')->unsigned()->comment('Объект, с которого планируется поставка')->after('supply_planning_id');
            $table->foreign('source_project_object_id')->references('id')->on('project_objects');

            $table->double('weight')->unsigned()->comment('Планируемый вес завоза')->after('source_project_object_id');

            $table->integer('standard_id')->unsigned()->after('id')->comment('Эталон');
            $table->foreign('standard_id')->references('id')->on('q3w_material_standards');

            $table->dropForeign('q3w_material_supply_materials_material_id_foreign');
            $table->dropColumn('material_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('q3w_material_supply_materials', function (Blueprint $table) {

            $table->dropForeign('q3w_material_supply_materials_source_project_object_id_foreign');
            $table->dropColumn('source_project_object_id');

            $table->dropColumn('weight');

            $table->dropForeign('q3w_material_supply_materials_standard_id_foreign');
            $table->dropColumn('standard_id');

            $table->bigInteger('material_id')->unsigned()->comment('Выбранный материал');
            $table->foreign('material_id')->references('id')->on('q3w_materials');
        });

        Schema::table('q3w_material_supply_planning', function (Blueprint $table) {
            $table->dropForeign('q3w_material_supply_planning_planning_object_id_foreign');
            $table->dropColumn('planning_object_id');

            $table->integer('project_object_id')->unsigned()->nullable()->comment('Идентификатор объекта');
            $table->foreign('project_object_id')->references('id')->on('projects');

            $table->dropForeign('q3w_material_supply_planning_brand_type_id_foreign');
            $table->dropColumn('brand_type_id');

            $table->integer('brand_id')->unsigned()->nullable()->comment('Идентификатор объекта');
            $table->foreign('brand_id')->references('id')->on('q3w_material_brands');

            $table->float('amount')->unsigned();
        });

        Schema::dropIfExists('q3w_material_supply_objects');
    }
};
