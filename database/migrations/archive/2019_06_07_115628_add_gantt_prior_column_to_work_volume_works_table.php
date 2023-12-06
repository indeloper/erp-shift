<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGanttPriorColumnToWorkVolumeWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_volume_works', function (Blueprint $table) {
            $table->unsignedInteger('gantt_prior')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_volume_works', function (Blueprint $table) {
            $table->dropColumn('gantt_prior');
        });
    }
}
