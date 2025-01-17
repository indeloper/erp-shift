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
        // column need to know
        Schema::table('material_accounting_bases', function (Blueprint $table) {
            $table->boolean('transferred_today')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_accounting_bases', function (Blueprint $table) {
            $table->dropColumn('transferred_today');
        });
    }
};
