<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResponsibleColumnToFuelTanksTablle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->unsignedInteger('responsible_id')->nullable()->after('object_id')->comment('ID ответственного');
            $table->foreign('responsible_id')->references('id')->on('users');

            $table->unsignedInteger('company_id')->nullable()->after('responsible_id')->comment('ID организации-собственника');
            $table->foreign('company_id')->references('id')->on('companies');
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
            $table->dropForeign(['responsible_id']);
            $table->dropColumn('responsible_id');

            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
}
