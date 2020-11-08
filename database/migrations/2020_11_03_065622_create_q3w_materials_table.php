<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQ3wMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('q3w_materials', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->bigInteger('standard_id')->unsigned()->comment('Идентификатор эталона')->index();
            $table->integer('project_object')->unsigned()->comment('Идентификатор объекта')->index();
            $table->integer('amount')->unsigned()->nullable()->comment('Количество в штуках (для штучного учета)');
            $table->double('quantity')->unsigned()->comment('Количество в единицах измерения');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('q3w_materials', function($table) {
            $table->foreign('standard_id')->references('id')->on('q3w_material_standards');
            $table->foreign('project_object')->references('id')->on('project_objects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('q3w_materials');
    }
}
