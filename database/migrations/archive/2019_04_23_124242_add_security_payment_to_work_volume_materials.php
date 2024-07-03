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
        Schema::table('work_volume_materials', function (Blueprint $table) {
            $table->float('security_price_one', 15, 2)->nullable();
            $table->float('security_price_result', 15, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_volume_materials', function (Blueprint $table) {
            $table->dropColumn('security_price_one');
            $table->dropColumn('security_price_result');
        });
    }
};
