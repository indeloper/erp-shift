<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTimesheetDayCategoriesShortnameField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('timesheet_day_categories', function (Blueprint $table) {
            $table->renameColumn('shortname', 'short_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('timesheet_day_categories', function (Blueprint $table) {
            // Откат переименования поля
            $table->renameColumn('short_name', 'shortname');
        });
    }
}
