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
        Schema::table('commercial_offers', function (Blueprint $table) {
            $table->renameColumn('gantt_image', 'is_uploaded');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commercial_offers', function (Blueprint $table) {
            $table->renameColumn('is_uploaded', 'gantt_image');
        });
    }
};
