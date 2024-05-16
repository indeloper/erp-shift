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
        Schema::table('tasks', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('work_volumes', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('commercial_offers', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('contracts', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('contractors', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('work_volumes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('commercial_offers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('contractors', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
