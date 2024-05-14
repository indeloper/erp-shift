<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateJobCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        Schema::create('job_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name');
            $table->unsignedInteger('report_group_id')->nullable()->index();
            $table->unsignedInteger('user_id')->index();
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
        Schema::dropIfExists('job_categories');
    }
}
