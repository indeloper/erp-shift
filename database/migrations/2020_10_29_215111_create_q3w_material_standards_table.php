<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQ3wMaterialStandardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('q3w_material_standards', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->string('name')->comment('Наименование эталона');
            $table->bigInteger('material_type')->unsigned()->comment('Тип материала');
            $table->double('weight')->unsigned()->comment('Вес за 1 единицу измерения');
            $table->text('description')->nullable()->comment('Описание');

            $table->timestamps();
            $table->softDeletes();

            $table->index('material_type');
        });

        Schema::table('q3w_material_standards', function($table) {
            $table->foreign('material_type')->references('id')->on('q3w_material_types');
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('q3w_material_standards');
    }
}
