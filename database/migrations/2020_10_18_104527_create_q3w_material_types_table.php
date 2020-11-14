<?php

use App\Models\q3wMaterial\q3wMaterialAccountingType;
use App\Models\q3wMaterial\q3wMaterialType;
use App\Models\q3wMaterial\q3wMeasureUnit;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQ3wMaterialTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('q3w_measure_units', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->string('value')->comment('Наименование');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('q3w_material_accounting_types', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->string('value')->comment('Наименование');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('q3w_material_types', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->string('name')->comment('Наименование');
            $table->text('description')->nullable()->comment('Описание');
            $table->bigInteger('measure_unit')->unsigned()->comment('Основная единица измерения');
            $table->text('measure_instructions')->nullable()->comment('Инструкция по измерению материала');
            $table->bigInteger('accounting_type')->unsigned()->comment('Тип учета');

            $table->timestamps();
            $table->softDeletes();

            $table->index('measure_unit');
            $table->index('accounting_type');
        });

        Schema::table('q3w_material_types', function($table) {
            $table->foreign('measure_unit')->references('id')->on('q3w_measure_units');
            $table->foreign('accounting_type')->references('id')->on('q3w_material_accounting_types');
        });

        $measuresUnitsNames = ['м.п', 'м²', 'м³', 'шт', 'т'];

        foreach ($measuresUnitsNames as $measuresUnitsName) {
            $newMeasureUnit = new q3wMeasureUnit();
            $newMeasureUnit -> value = $measuresUnitsName;
            $newMeasureUnit -> save();
        }

        $accountingTypeNames = ['Штучный', 'По единице измерения(?)'];

        foreach ($accountingTypeNames as $accountingTypeName) {
            $accountingType = new q3wMaterialAccountingType();
            $accountingType -> value = $accountingTypeName;
            $accountingType -> save();
        }

        $materialCategories = App\Models\Manual\ManualMaterialCategory::all();

        foreach ($materialCategories as $materialCategory) {
            $materialType = new q3wMaterialType();
            $materialType->name = $materialCategory->name;
            $materialType->description = $materialCategory->description;
            $materialType->measure_unit = q3wMeasureUnit::where('value', $materialCategory->category_unit)->first()->id;
            $materialType->accounting_type = 1;
            $materialType->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('q3w_material_types');
        Schema::dropIfExists('q3w_measure_units');
        Schema::dropIfExists('q3w_material_accounting_types');
    }
}
