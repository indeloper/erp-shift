<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnBitrixIdToProjectObjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_objects', function (Blueprint $table) {
            $table->bigInteger('bitrixId')->nullable()->after('id')->comment('Id в Битрикс');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_objects', function (Blueprint $table) {
            $table->dropColumn('bitrixId');
        });
    }
}
