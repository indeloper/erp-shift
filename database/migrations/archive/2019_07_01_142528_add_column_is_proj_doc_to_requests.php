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
        Schema::table('work_volume_request_files', function (Blueprint $table) {
            $table->boolean('is_proj_doc')->default(0);
        });

        Schema::table('commercial_offer_request_files', function (Blueprint $table) {
            $table->boolean('is_proj_doc')->default(0);
        });

        Schema::table('contract_request_files', function (Blueprint $table) {
            $table->boolean('is_proj_doc')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_volume_request_files', function (Blueprint $table) {
            $table->dropColumn('is_proj_doc');
        });

        Schema::table('commercial_offer_request_files', function (Blueprint $table) {
            $table->dropColumn('is_proj_doc');
        });

        Schema::table('contract_request_files', function (Blueprint $table) {
            $table->dropColumn('is_proj_doc');
        });
    }
};
