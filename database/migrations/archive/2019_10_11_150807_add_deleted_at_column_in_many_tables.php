<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletedAtColumnInManyTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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
     *
     * @return void
     */
    public function down()
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
}
