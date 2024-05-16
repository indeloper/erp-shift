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
        Schema::table('contractor_files', function (Blueprint $table) {
            $table->unsignedInteger('commercial_offer_id');
            $table->unsignedInteger('contractor_id');
            $table->string('file_name');
            $table->string('original_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contractor_files', function (Blueprint $table) {
            $table->dropColumn('commercial_offer_id');
            $table->dropColumn('contractor_id');
            $table->dropColumn('file_name');
            $table->dropColumn('original_name');
        });
    }
};
