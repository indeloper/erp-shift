<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsNodeColumnInWorkVolumeMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_volume_materials', function (Blueprint $table) {
            $table->boolean('is_node')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_volume_materials', function (Blueprint $table) {
            $table->dropColumn('is_node');
        });
    }
}
