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
        Schema::table('file_entries', function (Blueprint $table) {
            $table->integer('documentable_id')->nullable();
            $table->string('documentable_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_entries', function (Blueprint $table) {
            $table->dropColumn(['documentable_id', 'documentable_type']);
        });
    }
};
