<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lexx\ChatMessenger\Models\Models;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(Models::table('threads'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('subject')->nullable();
            $table->integer('max_participants')->nullable()->comment('Max number of participants allowed');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('avatar')->nullable()->comment('Profile picture for the conversation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Models::table('threads'));
    }
};
