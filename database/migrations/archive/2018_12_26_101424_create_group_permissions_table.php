<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('group_id')->nullable();
            $table->unsignedInteger('permission_id')->nullable();
            $table->timestamps();
        });

        Schema::table('group_permissions', function (Blueprint $table) {
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('group_permissions', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropForeign(['permission_id']);
        });
        
        Schema::drop('group_permissions');
    }
}
