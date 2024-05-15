<?php

use App\Models\q3wMaterial\q3wProjectObjectMaterialAccountingType;
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
        Schema::create('q3w_materials', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->integer('standard_id')->unsigned()->comment('Идентификатор эталона')->index();
            $table->integer('project_object')->unsigned()->comment('Идентификатор объекта')->index();
            $table->integer('amount')->unsigned()->comment('Количество (в штуках)');
            $table->double('quantity')->unsigned()->comment('Количество (в единицах измерения)');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('q3w_materials', function ($table) {
            $table->foreign('standard_id')->references('id')->on('q3w_material_standards');
            $table->foreign('project_object')->references('id')->on('project_objects');
        });

        Schema::create('q3w_project_object_material_accounting_types', function (Blueprint $table) {
            $table->integerIncrements('id')->comment('Уникальный идентификатор');
            $table->string('name')->comment('Наименование');

            $table->timestamps();
            $table->softDeletes();
        });

        $projectObjectMaterialAccountingTypeNames = ['Производство', 'Склад'];

        foreach ($projectObjectMaterialAccountingTypeNames as $projectObjectMaterialAccountingTypeName) {
            $projectObjectMaterialAccountingType = new q3wProjectObjectMaterialAccountingType();
            $projectObjectMaterialAccountingType->name = $projectObjectMaterialAccountingTypeName;
            $projectObjectMaterialAccountingType->save();
        }

        Schema::table('project_objects', function (Blueprint $table) {
            $table->integer('material_accounting_type')->unsigned()->default(1)->comment('Идентификатор типа материального учета объекта')->index();
        });

        Schema::table('project_objects', function ($table) {
            $table->foreign('material_accounting_type')->references('id')->on('q3w_project_object_material_accounting_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumn('project_objects', 'material_accounting_type')) {
            Schema::table('project_objects', function ($table) {
                $table->dropForeign('project_objects_material_accounting_type_foreign');
                $table->dropColumn('material_accounting_type');
            });
        }

        Schema::dropIfExists('q3w_project_object_material_accounting_types');
        Schema::dropIfExists('q3w_materials');
    }
};
