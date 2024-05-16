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
        Schema::create('object_responsible_users', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('object_id');
            $table->unsignedInteger('user_id');
            $table->integer('role');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('object_responsible_users');
    }
};
