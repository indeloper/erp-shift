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
        Schema::table('work_volume_materials', function (Blueprint $table) {
            $table->unsignedInteger('subcontractor_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_volume_materials', function (Blueprint $table) {
            $table->dropColumn('subcontractor_id');
        });
    }
};
