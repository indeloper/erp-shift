<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaterialTypeColumnToWorkVolumeMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_volume_materials', function (Blueprint $table) {
            $table->string('material_type')->default('regular');
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
            $table->dropColumn('material_type');
        });
    }
}
