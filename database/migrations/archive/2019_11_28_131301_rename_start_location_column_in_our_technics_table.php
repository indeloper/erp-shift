<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameStartLocationColumnInOurTechnicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('our_technics', function (Blueprint $table) {
            $table->renameColumn('start_location', 'start_location_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('our_technics', function (Blueprint $table) {
            $table->renameColumn('start_location_id', 'start_location');
        });
    }
}
