<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->unsignedInteger('status')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('department_id')->nullable();
            $table->unsignedInteger('group_id')->nullable();
            $table->boolean('is_seen')->default(0);
            $table->unsignedInteger('task_id')->nullable();
            $table->unsignedInteger('voice_url')->nullable();
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
        Schema::dropIfExists('notifications');
    }
}