<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobCategoryTariffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        Schema::create('job_category_tariffs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('job_category_id')->index();
            $table->unsignedInteger('tariff_id')->index();
            $table->unsignedInteger('user_id')->index();
            $table->float('rate', 8, 2);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_category_tariffs');
    }
}