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
            $table->string('document')->nullable()->after('volume')->comment('Реквизиты документа');
            $table->date('document_date')->nullable()->after('document')->comment('Дата документа');
        });

        Schema::table('fuel_tank_flows', function (Blueprint $table) {
            $table->dropColumn('comment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_tank_flows', function (Blueprint $table) {
            $table->dropColumn('document');
            $table->dropColumn('document_date');
        });
    }
};
