<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewSaveColumnsToWorkVolumes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_volumes', function (Blueprint $table) {
            $table->unsignedInteger('is_save_tongue')->default(0);
            $table->unsignedInteger('is_save_pile')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_volumes', function (Blueprint $table) {
            $table->dropColumn('is_save_tongue');
            $table->dropColumn('is_save_pile');
        });
    }
}
