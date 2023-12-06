<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTechnicCategoryCategoryCharacteristicTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technic_category_category_characteristic', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('technic_category_id');
            $table->integer('category_characteristic_id');
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
        Schema::dropIfExists('technic_category_category_characteristic');
    }
}
