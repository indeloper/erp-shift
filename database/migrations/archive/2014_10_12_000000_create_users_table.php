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
        //  Schema::create('users', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->string('first_name')->nullable();
        //     $table->string('last_name')->nullable();
        //     $table->string('patronymic')->nullable();
        //     $table->date('birthday')->nullable();
        //     $table->string('email')->unique();
        //     $table->string('person_phone')->unique()->nullable();
        //     $table->string('work_phone')->unique()->nullable();
        //     $table->unsignedInteger('department_id')->nullable();
        //     $table->unsignedInteger('group_id')->nullable();
        //     $table->string('image')->nullable();
        //     $table->string('password');
        //     $table->boolean('status')->default(1);
        //     $table->boolean('is_su')->default(0);
        //     $table->rememberToken();
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('users');
    }
};
