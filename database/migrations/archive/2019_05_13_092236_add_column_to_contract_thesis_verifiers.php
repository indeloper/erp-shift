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
        Schema::table('contract_thesis_verifiers', function (Blueprint $table) {
            $table->unsignedInteger('thesis_id');
            $table->string('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_thesis_verifiers', function (Blueprint $table) {
            $table->dropColumn('thesis_id');
            $table->dropColumn('status');
        });
    }
};
