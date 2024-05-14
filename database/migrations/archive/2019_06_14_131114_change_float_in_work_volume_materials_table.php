<?php

use Illuminate\Database\Migrations\Migration;

class ChangeFloatInWorkVolumeMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE work_volume_materials CHANGE COLUMN count count DOUBLE(10, 3) NULL DEFAULT NULL ;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE work_volume_materials CHANGE COLUMN count count DOUBLE(10, 2) NULL DEFAULT NULL ;');
    }
}
