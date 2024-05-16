<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuel_tank_movements', function (Blueprint $table) {
            $table->unsignedInteger('previous_object_id')->nullable()->after('object_id')->comment('ID предыдущего объекта');
            $table->foreign('previous_object_id')->references('id')->on('project_objects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fuel_tank_movements', function (Blueprint $table) {
            $table->dropForeign(['previous_object_id']);
            $table->dropColumn('previous_object_id');
        });
    }
};
