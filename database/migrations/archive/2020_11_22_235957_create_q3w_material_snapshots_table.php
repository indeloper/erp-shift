<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQ3wMaterialSnapshotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('q3w_material_snapshots', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->bigInteger('operation_id')->index()->unsigned()->comment('Идентификатор операции');
            $table->integer('project_object_id')->index()->unsigned()->comment('Идентификатор объекта');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('operation_id')->references('id')->on('q3w_material_operations');
            $table->foreign('project_object_id')->references('id')->on('project_objects');
        });

        Schema::create('q3w_material_snapshot_materials', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->bigInteger('snapshot_id')->unsigned()->index()->comment('Идентификатор снапшота');
            $table->integer('standard_id')->unsigned()->index()->comment('Идентификатор эталона');
            $table->integer('amount')->unsigned()->nullable()->comment('Количество в штуках');
            $table->double('quantity')->unsigned()->comment('Количество в единицах измерения');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('snapshot_id')->references('id')->on('q3w_material_snapshots');
            $table->foreign('standard_id')->references('id')->on('q3w_material_standards');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('q3w_material_snapshot_materials');
        Schema::dropIfExists('q3w_material_snapshots');
    }
}