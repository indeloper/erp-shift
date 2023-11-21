<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommentMovementTmpColumnToFuelTanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->string('comment_movement_tmp')->nullable()->after('explotation_start')->comment('Временный комментарий при передаче емкости');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->dropColumn('comment_movement_tmp');
        });
    }
}
