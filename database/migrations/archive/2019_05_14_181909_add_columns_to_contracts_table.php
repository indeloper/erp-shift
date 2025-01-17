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
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('garant_file_name')->nullable();
            $table->string('final_file_name')->nullable();
            $table->unsignedInteger('contract_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('garant_file_name');
            $table->dropColumn('contract_id');
            $table->dropColumn('final_file_name');

        });
    }
};
