<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_responsible_user_redirect_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('vacation_id');
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('old_user_id');
            $table->unsignedInteger('new_user_id');
            $table->unsignedInteger('role');
            $table->string('reason')->nullable();
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
        Schema::dropIfExists('project_responsible_user_redirect_histories');
    }
};
