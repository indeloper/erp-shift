<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('material_accounting_operations', function (Blueprint $table) {
            $table->unsignedInteger('supplier_id')->nullable()->after('recipient_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('material_accounting_operations', function (Blueprint $table) {
            $table->dropColumn('supplier_id');
        });
    }
};
