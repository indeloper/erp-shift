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
        Schema::table('project_objects', function (Blueprint $table) {
            $table->bigInteger('bitrixId')->nullable()->after('id')->comment('Id в Битрикс');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_objects', function (Blueprint $table) {
            $table->dropColumn('bitrixId');
        });
    }
};
