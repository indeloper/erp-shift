<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_files', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('task_id');
            $table->unsignedInteger('user_id');
            $table->string('file_name');
            $table->string('original_name');
            $table->unsignedInteger('is_final')->default(0);
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
        Schema::dropIfExists('task_files');
    }
}