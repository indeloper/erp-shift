<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimecardAdditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timecard_additions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('timecard_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedInteger('type');
            $table->string('name', 500);
            $table->double('amount', 10, 2);
            $table->boolean('prolonged')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['timecard_id', 'type', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timecard_additions');
    }
}