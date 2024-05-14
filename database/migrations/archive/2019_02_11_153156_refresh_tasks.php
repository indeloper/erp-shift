<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefreshTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('tasks');

        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedInteger('project_id')->nullable();
            $table->unsignedInteger('contractor_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('responsible_user_id')->nullable();
            $table->unsignedInteger('contact_id')->nullable();
            $table->string('incoming_phone')->nullable();
            $table->string('internal_phone')->nullable();
            $table->unsignedInteger('status_result_call')->nullable();
            $table->text('final_note')->nullable();
            $table->boolean('is_seen')->default(0);
            $table->unsignedInteger('status_result')->nullable();
            $table->unsignedInteger('status')->default(1);
            $table->unsignedInteger('is_solved')->default(0);
            $table->datetime('expired_at');
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
        Schema::dropIfExists('tasks');
    }
}
