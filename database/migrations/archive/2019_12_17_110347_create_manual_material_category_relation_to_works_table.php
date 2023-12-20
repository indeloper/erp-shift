<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManualMaterialCategoryRelationToWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manual_material_category_relation_to_works', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('manual_material_category_id');
            $table->unsignedInteger('work_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manual_material_category_relation_to_works');
    }
}
