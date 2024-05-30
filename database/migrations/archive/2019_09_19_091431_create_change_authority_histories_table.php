<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('change_authority_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('vacation_id');
            $table->unsignedInteger('old_group_id');
            $table->unsignedInteger('old_department_id');
            $table->unsignedInteger('new_group_id');
            $table->unsignedInteger('new_department_id');
            $table->unsignedInteger('user_id');
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_authority_histories');
    }
};
