<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCheckboxToManualNodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manual_nodes', function (Blueprint $table) {
            $table->boolean('is_compact_wv')->default(0);
            $table->boolean('is_compact_cp')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('manual_nodes', function (Blueprint $table) {
            $table->dropColumn('is_compact_wv');
            $table->dropColumn('is_compact_cp');
        });
    }
}