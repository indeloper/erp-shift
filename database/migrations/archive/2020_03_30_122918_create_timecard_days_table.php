<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimecardDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timecard_days', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('timecard_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('day');
            $table->boolean('is_opened')->default(0);
            $table->boolean('completed')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['timecard_id', 'day']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timecard_days');
    }
}
