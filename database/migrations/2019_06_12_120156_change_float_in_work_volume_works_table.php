<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFloatInWorkVolumeWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE work_volume_works CHANGE COLUMN count count DOUBLE(10, 3) NULL DEFAULT NULL ;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE work_volume_works CHANGE COLUMN count count DOUBLE(10, 2) NULL DEFAULT NULL ;');
    }
}
