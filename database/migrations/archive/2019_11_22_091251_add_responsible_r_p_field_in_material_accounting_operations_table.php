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
            $table->unsignedInteger('responsible_RP')->nullable()->after('supplier_id');
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
            $table->dropColumn('responsible_RP');
        });
    }
};
