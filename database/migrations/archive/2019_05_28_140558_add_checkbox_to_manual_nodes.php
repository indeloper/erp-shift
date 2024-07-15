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
        Schema::table('manual_nodes', function (Blueprint $table) {
            $table->boolean('is_compact_wv')->default(0);
            $table->boolean('is_compact_cp')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manual_nodes', function (Blueprint $table) {
            $table->dropColumn('is_compact_wv');
            $table->dropColumn('is_compact_cp');
        });
    }
};
