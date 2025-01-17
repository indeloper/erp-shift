<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('timesheet_day_categories', function (Blueprint $table) {
            $table->renameColumn('shortname', 'short_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timesheet_day_categories', function (Blueprint $table) {
            // Откат переименования поля
            $table->renameColumn('short_name', 'shortname');
        });
    }
};
