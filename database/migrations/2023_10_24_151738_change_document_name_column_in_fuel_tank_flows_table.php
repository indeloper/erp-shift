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
            $table->dropColumn('document_date');
            $table->date('event_date')->after('id')->comment('Дата время факта события');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_tank_flows', function (Blueprint $table) {
            $table->date('document_date');
            $table->dropColumn('event_date');
        });
    }
};
