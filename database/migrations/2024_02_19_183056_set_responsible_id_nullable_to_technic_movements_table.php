<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetResponsibleIdNullableToTechnicMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('technic_movements', function (Blueprint $table) {
            $table->dropForeign(['responsible_id']);
        });

        Schema::table('technic_movements', function (Blueprint $table) {
            $table->unsignedInteger('responsible_id')->nullable()->change();
            $table->foreign('responsible_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('technic_movements', function (Blueprint $table) {
            //
        });
    }
}
