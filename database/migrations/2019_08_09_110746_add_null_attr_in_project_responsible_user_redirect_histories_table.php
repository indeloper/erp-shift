<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullAttrInProjectResponsibleUserRedirectHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_responsible_user_redirect_histories', function (Blueprint $table) {
            $table->unsignedInteger('vacation_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_responsible_user_redirect_histories', function (Blueprint $table) {
            //
        });
    }
}
