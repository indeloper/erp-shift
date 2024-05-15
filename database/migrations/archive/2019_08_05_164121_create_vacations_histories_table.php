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
        Schema::create('vacations_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('vacation_user_id');
            $table->unsignedInteger('support_user_id');
            $table->string('from_date')->nullable();
            $table->string('by_date')->nullable();
            $table->string('return_date')->nullable();
            $table->boolean('is_actual')->default(1);
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
        Schema::dropIfExists('vacations_histories');
    }
};
