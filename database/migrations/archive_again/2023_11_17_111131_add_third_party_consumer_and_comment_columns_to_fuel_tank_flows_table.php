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
        Schema::table('fuel_tank_flows', function (Blueprint $table) {
            $table->string('third_party_consumer')->nullable()->after('our_technic_id')->comment('Сторонний потребитель топлива');
            $table->string('comment')->nullable()->after('document')->comment('Комментарий');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_tank_flows', function (Blueprint $table) {
            $table->dropColumn('third_party_consumer');
            $table->dropColumn('comment');
        });
    }
};
