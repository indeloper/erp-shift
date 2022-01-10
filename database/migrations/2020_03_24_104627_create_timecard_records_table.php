<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimecardRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        Schema::create('timecard_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('timecard_day_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('type');
            $table->unsignedInteger('tariff_id')->nullable();
            $table->unsignedInteger('project_id')->nullable();
            $table->double('length', 8, 3)->nullable();
            $table->integer('amount')->nullable();
            $table->string('start')->nullable();
            $table->string('end')->nullable();
            $table->string('commentary')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['timecard_day_id', 'type']);
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
        Schema::dropIfExists('timecard_records');
    }
}
