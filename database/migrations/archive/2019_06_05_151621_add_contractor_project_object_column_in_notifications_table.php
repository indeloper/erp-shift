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
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedInteger('contractor_id')->after('user_id')->nullable();
            $table->unsignedInteger('project_id')->after('contractor_id')->nullable();
            $table->unsignedInteger('object_id')->after('project_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('contractor_id');
            $table->dropColumn('project_id');
            $table->dropColumn('object_id');
        });
    }
};
