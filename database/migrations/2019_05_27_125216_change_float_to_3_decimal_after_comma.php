<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFloatTo3DecimalAfterComma extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_volume_works', function (Blueprint $table) {
            $table->float('count', 10, 3)->nullable()->change();
        });

        Schema::table('work_volume_materials', function (Blueprint $table) {
            $table->float('count', 10, 3)->nullable()->change();
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
            $table->float('count', 10, 2)->nullable()->change();
        });

        Schema::table('work_volume_materials', function (Blueprint $table) {
            $table->float('count', 10, 2)->nullable()->change();
        });
    }
}
