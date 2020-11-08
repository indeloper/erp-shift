<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChangeAuthorityColumnInVacationsHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vacations_histories', function (Blueprint $table) {
            $table->boolean('change_authority')->default(0)->after('is_actual');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vacations_histories', function (Blueprint $table) {
            $table->dropColumn('change_authority');
        });
    }
}
