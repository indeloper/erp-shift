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
        Schema::table('project_objects', function (Blueprint $table) {
            $table->renameColumn('bitrixId', 'bitrix_id');
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
            $table->renameColumn('bitrix_id', 'bitrixId');
        });
    }
};
