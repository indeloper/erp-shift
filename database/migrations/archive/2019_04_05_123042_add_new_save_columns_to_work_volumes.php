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
        Schema::table('work_volumes', function (Blueprint $table) {
            $table->unsignedInteger('is_save_tongue')->default(0);
            $table->unsignedInteger('is_save_pile')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_volumes', function (Blueprint $table) {
            $table->dropColumn('is_save_tongue');
            $table->dropColumn('is_save_pile');
        });
    }
};
