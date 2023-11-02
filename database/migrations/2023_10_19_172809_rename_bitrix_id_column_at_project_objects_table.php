<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameBitrixIdColumnAtProjectObjectsTable extends Migration
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
}
