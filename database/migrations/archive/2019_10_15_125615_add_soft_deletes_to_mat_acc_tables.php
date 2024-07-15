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
        Schema::table('material_accounting_operations', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('material_accounting_operation_materials', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('material_accounting_material_files', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_accounting_operations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('material_accounting_operation_materials', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('material_accounting_material_files', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
