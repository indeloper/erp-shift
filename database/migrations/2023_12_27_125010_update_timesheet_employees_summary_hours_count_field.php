<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTimesheetEmployeesSummaryHoursCountField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('timesheet_employees_summary_hours', function (Blueprint $table) {
            $table->integer('count')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('timesheet_employees_summary_hours', function (Blueprint $table) {
            $table->integer('count')->nullable(false)->change();
        });
    }
}
